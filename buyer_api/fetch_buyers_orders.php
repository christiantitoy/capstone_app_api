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
    // Comprehensive query: orders → items → item_variants → stores → sellers
    // FIXED: Changed o.discount to o.platform_fee
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
            o.platform_fee,              -- ✅ FIXED: Changed from discount to platform_fee
            o.total_amount,
            o.status                AS order_status,
            o.created_at            AS order_created_at,
            o.updated_at            AS order_updated_at,
            o.locked_at,

            -- Buyer Address
            ba.recipient_name,
            ba.phone_number,
            ba.full_address,
            ba.gps_location,
            ba.is_default,

            -- Order Item
            oi.id                   AS item_id,
            oi.product_id,
            oi.variation_id,
            oi.selected_options,        -- usually JSON string
            oi.quantity,
            oi.unit_price,
            oi.total_price,

            -- Item (formerly products)
            i.product_name,
            i.category,
            i.main_image_url,
            i.image_urls                AS product_image_urls,
            i.has_variations,

            -- Item Variant (formerly product_variants)
            iv.options_json,
            iv.options_json_value,
            iv.price                    AS variant_price,
            iv.sku,
            iv.image_urls               AS variant_image_urls,

            -- Store (formerly seller_profiles)
            s.store_name,
            s.category                  AS store_category,
            s.description               AS store_description,
            s.contact_number,
            s.logo_url,
            s.banner_url,
            s.owner_full_name,
            
            -- Seller (user account)
            sel.full_name               AS seller_fullname,
            sel.email                   AS seller_email

        FROM orders o
        LEFT JOIN buyer_addresses ba 
            ON o.address_id = ba.id
        LEFT JOIN order_items oi 
            ON o.id = oi.order_id
        LEFT JOIN items i
            ON oi.product_id = i.id
        LEFT JOIN item_variants iv
            ON oi.variation_id = iv.id
        LEFT JOIN stores s
            ON i.seller_id = s.seller_id
        LEFT JOIN sellers sel
            ON i.seller_id = sel.id
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
                'addressId'     => isset($row['address_id']) ? (int)$row['address_id'] : null,
                'paymentMethod' => $row['payment_method'],
                'subtotal'      => (float)$row['subtotal'],
                'shippingFee'   => (float)$row['shipping_fee'],
                'platformFee'   => (float)($row['platform_fee'] ?? 0),  // ✅ FIXED: Changed from discount
                'totalAmount'   => (float)$row['total_amount'],
                'status'        => $row['order_status'],
                'createdAt'     => $row['order_created_at'],
                'updatedAt'     => $row['order_updated_at'],
                'lockedAt'      => $row['locked_at'],

                // Address fields
                'recipientName'  => $row['recipient_name']  ?? null,
                'phoneNumber'    => $row['phone_number']   ?? null,
                'fullAddress'    => $row['full_address']   ?? null,
                'gpsLocation'    => $row['gps_location']   ?? null,
                'isDefault'      => (bool)($row['is_default'] ?? false),

                // Display fields for frontend convenience
                'city'           => 'Dumaguete City',
                'province'       => 'Negros Oriental',
                'zipCode'        => '6200',

                'items' => []
            ];
        }

        if ($row['item_id'] === null) continue;

        // Handle product_image_urls safely (could be JSON or comma-separated)
        $productImageUrls = [];
        if (!empty($row['product_image_urls'])) {
            if (is_string($row['product_image_urls'])) {
                // Try to decode as JSON first
                $decoded = json_decode($row['product_image_urls'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $productImageUrls = $decoded;
                } else {
                    // Fallback to comma-separated
                    $productImageUrls = explode(',', $row['product_image_urls']);
                }
            }
        }

        $item = [
            'itemId'          => (int)$row['item_id'],
            'productId'       => (int)$row['product_id'],
            'variationId'     => $row['variation_id'] ? (int)$row['variation_id'] : null,
            'selectedOptions' => $row['selected_options'] ?? null,
            'quantity'        => (int)$row['quantity'],
            'unitPrice'       => (float)$row['unit_price'],
            'totalPrice'      => (float)$row['total_price'],

            // Item (product) details
            'productName'     => $row['product_name']     ?? '[Item Removed]',
            'category'        => $row['category']         ?? null,
            'mainImageUrl'    => $row['main_image_url']   ?? null,
            'productImageUrls'=> $productImageUrls,
            'hasVariations'   => (bool)($row['has_variations'] ?? false),

            // Variant (only if variation_id was used)
            'variant'         => null,
        ];

        if ($row['variation_id']) {
            // Handle variant_image_urls safely
            $variantImageUrls = [];
            if (!empty($row['variant_image_urls'])) {
                if (is_string($row['variant_image_urls'])) {
                    $decoded = json_decode($row['variant_image_urls'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $variantImageUrls = $decoded;
                    } else {
                        $variantImageUrls = explode(',', $row['variant_image_urls']);
                    }
                }
            }

            $item['variant'] = [
                'sku'                => $row['sku'] ?? null,
                'optionsJson'        => $row['options_json'] ?? null,
                'optionsJsonValue'   => $row['options_json_value'] ?? null,
                'variantPrice'       => $row['variant_price'] ? (float)$row['variant_price'] : null,
                'variantImageUrls'   => $variantImageUrls,
            ];
        }

        // Store & Seller information
        $item['store'] = [
            'storeName'      => $row['store_name']        ?? null,
            'storeCategory'  => $row['store_category']    ?? null,
            'description'    => $row['store_description'] ?? null,
            'contactNumber'  => $row['contact_number']    ?? null,
            'logoUrl'        => $row['logo_url']          ?? null,
            'bannerUrl'      => $row['banner_url']        ?? null,
            'ownerFullName'  => $row['owner_full_name']   ?? null,
        ];
        
        $item['seller'] = [
            'fullName'      => $row['seller_fullname']  ?? null,
            'email'         => $row['seller_email']     ?? null,
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
        'message' => 'Database error occurred',
        'debug' => $e->getMessage(),  // Remove in production
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
?>