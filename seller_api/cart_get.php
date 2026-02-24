<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    if (!isset($_GET['buyer_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Buyer ID is required'
        ]);
        exit;
    }

    $buyer_id = intval($_GET['buyer_id']);

    $sql = "SELECT 
                ci.id AS cart_item_id,
                ci.product_id,
                ci.variation_id,
                ci.selected_options,
                ci.quantity,
                ci.unit_price,
                ci.is_purchased,
                ci.added_at,
                ci.updated_at,
                p.product_name,
                p.product_description,
                p.stock,
                p.main_image_url,
                sp.shop_name,
                sp.id AS seller_id,
                pv.price AS variant_price,
                pv.stock AS variant_stock,
                pv.image_urls AS variant_images,
                pv.options_json_value
            FROM cart_items ci
            LEFT JOIN products p ON ci.product_id = p.id
            LEFT JOIN seller_profiles sp ON p.seller_id = sp.id
            LEFT JOIN product_variants pv ON ci.variation_id = pv.id
            WHERE ci.buyer_id = :buyer_id
            AND p.status = 'approved'
            AND ci.is_purchased = 0
            ORDER BY ci.added_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyer_id' => $buyer_id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $grouped_items = [];

    foreach ($rows as $row) {

        // Build unique key for grouping
        $key = $row['product_id'] . '|' .
               ($row['variation_id'] ?? 'null') . '|' .
               $row['selected_options'];

        // Determine correct price & stock
        $unit_price = !empty($row['variant_price'])
            ? floatval($row['variant_price'])
            : floatval($row['unit_price']);

        $stock = !empty($row['variant_stock'])
            ? intval($row['variant_stock'])
            : intval($row['stock']);

        // Determine correct image
        $image_url = $row['main_image_url'];
        if (!empty($row['variant_images'])) {
            $images = explode(',', $row['variant_images']);
            if (!empty($images[0])) {
                $image_url = $images[0];
            }
        }

        if (!isset($grouped_items[$key])) {
            // First appearance
            $grouped_items[$key] = [
                'cart_item_id' => (int)$row['cart_item_id'],
                'product_id' => (int)$row['product_id'],
                'product_name' => $row['product_name'],
                'variation_id' => $row['variation_id'] ? (int)$row['variation_id'] : null,
                'selected_options' => $row['selected_options'],
                'options_json_value' => $row['options_json_value'],
                'quantity' => (int)$row['quantity'],
                'unit_price' => $unit_price,
                'total_price' => $unit_price * (int)$row['quantity'],
                'stock' => $stock,
                'image_url' => $image_url,
                'shop_name' => $row['shop_name'],
                'seller_id' => (int)$row['seller_id'],
                'added_at' => $row['added_at'],
                'updated_at' => $row['updated_at'],
                'isPurchased' => $row['is_purchased'] == 1
            ];
        } else {
            // Stack quantity ONLY
            $grouped_items[$key]['quantity'] += (int)$row['quantity'];

            // Recompute total price (no summing)
            $grouped_items[$key]['total_price'] =
                $grouped_items[$key]['unit_price'] *
                $grouped_items[$key]['quantity'];
        }
    }

    echo json_encode([
        'status' => 'success',
        'cart_items' => array_values($grouped_items),
        'count' => count($grouped_items)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>