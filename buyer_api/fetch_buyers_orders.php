<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');     // tighten this in production!
header('Access-Control-Allow-Methods: GET');

require_once '/var/www/html/connection/db_connection.php';

try {
    if (!isset($_GET['buyer_id']) || !is_numeric($_GET['buyer_id'])) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Valid buyer_id (integer) is required'
        ]);
        exit;
    }

    $buyer_id = (int)$_GET['buyer_id'];

    // ────────────────────────────────────────────────────────────────
    // Comprehensive query: orders → items → product → variant → seller
    // ────────────────────────────────────────────────────────────────
    $sql = "
        SELECT 
            -- Order
            o.id                    AS order_id,
            o.buyer_id,
            o.address_id,
            o.payment_method,
            o.subtotal,
            o.shipping_fee,
            o.discount,
            o.total_amount,
            o.status                AS order_status,
            o.created_at            AS order_created_at,
            o.updated_at            AS order_updated_at,
            o.locked_at,

            -- Buyer Address
            ba.recipient_name,
            ba.phone_number,
            ba.barangay,
            ba.street_address,

            -- Order Item
            oi.id                   AS item_id,
            oi.product_id,
            oi.variation_id,
            oi.selected_options,        -- usually JSON string
            oi.quantity,
            oi.unit_price,
            oi.total_price,

            -- Product
            p.product_name,
            p.category,
            p.main_image_url,
            p.image_urls                AS product_image_urls,
            p.has_variations,

            -- Product Variant (if any)
            pv.options_json,
            pv.options_json_value,
            pv.price                    AS variant_price,
            pv.sku,
            pv.image_urls               AS variant_image_urls,

            -- Seller
            sp.shop_name,
            sp.fullname                 AS seller_fullname,
            sp.shop_category,
            sp.business_type

        FROM orders o
        LEFT JOIN buyer_addresses ba 
            ON o.address_id = ba.id
        LEFT JOIN order_items oi 
            ON o.id = oi.order_id
        LEFT JOIN products p
            ON oi.product_id = p.id
        LEFT JOIN product_variants pv
            ON oi.variation_id = pv.id
        LEFT JOIN seller_profiles sp
            ON p.seller_id = sp.id
        WHERE o.buyer_id = :buyer_id
        ORDER BY o.created_at DESC, oi.id ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyer_id' => $buyer_id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ────────────────────────────────────────────────────────────────
    // Group items under each order
    // ────────────────────────────────────────────────────────────────
    $ordersMap = [];

    foreach ($rows as $row) {
        $orderId = $row['order_id'];

        if (!isset($ordersMap[$orderId])) {
            $ordersMap[$orderId] = [
                'id'            => (int)$row['order_id'],
                'buyerId'       => (int)$row['buyer_id'],
                'addressId'     => (int)$row['address_id'],
                'paymentMethod' => $row['payment_method'],
                'subtotal'      => (float)$row['subtotal'],
                'shippingFee'   => (float)$row['shipping_fee'],
                'discount'      => (float)$row['discount'],
                'totalAmount'   => (float)$row['total_amount'],
                'status'        => $row['order_status'],
                'createdAt'     => $row['order_created_at'],
                'updatedAt'     => $row['order_updated_at'],
                'lockedAt'      => $row['locked_at'],

                'recipientName'  => $row['recipient_name']  ?? null,
                'phoneNumber'    => $row['phone_number']   ?? null,
                'barangay'       => $row['barangay']       ?? null,
                'streetAddress'  => $row['street_address'] ?? null,

                'items' => []
            ];
        }

        if ($row['item_id'] === null) continue;

        $item = [
            'itemId'          => (int)$row['item_id'],
            'productId'       => (int)$row['product_id'],
            'variationId'     => $row['variation_id'] ? (int)$row['variation_id'] : null,
            'selectedOptions' => $row['selected_options'] ?? null,       // usually JSON string
            'quantity'        => (int)$row['quantity'],
            'unitPrice'       => (float)$row['unit_price'],
            'totalPrice'      => (float)$row['total_price'],

            // Product
            'productName'     => $row['product_name']     ?? '[Product Removed]',
            'category'        => $row['category']         ?? null,
            'mainImageUrl'    => $row['main_image_url']   ?? null,
            'productImageUrls'=> $row['product_image_urls'] ? explode(',', $row['product_image_urls']) : [],
            'hasVariations'   => (bool)($row['has_variations'] ?? 0),

            // Variant (only if variation_id was used)
            'variant'         => null,
        ];

        if ($row['variation_id']) {
            $item['variant'] = [
                'sku'                => $row['sku'] ?? null,
                'optionsJson'        => $row['options_json']        ?? null,   // jsonb → usually assoc array after json_decode
                'optionsJsonValue'   => $row['options_json_value']  ?? null,
                'variantPrice'       => $row['variant_price'] ? (float)$row['variant_price'] : null,
                'variantImageUrls'   => $row['variant_image_urls'] ? explode(',', $row['variant_image_urls']) : [],
            ];
        }

        // Seller (same for all items of the same product → but we attach per item)
        $item['seller'] = [
            'shopName'      => $row['shop_name']        ?? null,
            'sellerName'    => $row['seller_fullname']  ?? null,
            'shopCategory'  => $row['shop_category']    ?? null,
            'businessType'  => $row['business_type']    ?? null,
        ];

        $ordersMap[$orderId]['items'][] = $item;
    }

    $formattedOrders = array_values($ordersMap);

    echo json_encode([
        'status'   => 'success',
        'buyerId'  => $buyer_id,
        'orders'   => $formattedOrders,
        'count'    => count($formattedOrders),
        'timestamp' => date('c'),
    ], JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error occurred'
        // 'debug' => $e->getMessage()   // remove in production
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}