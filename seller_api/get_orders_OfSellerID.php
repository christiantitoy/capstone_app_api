<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get seller_id from POST or GET
    $seller_id = $_POST['seller_id'] ?? $_GET['seller_id'] ?? null;

    // Optional filters
    $status = $_POST['status'] ?? $_GET['status'] ?? null;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : (isset($_GET['limit']) ? intval($_GET['limit']) : 20);
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : (isset($_GET['offset']) ? intval($_GET['offset']) : 0);

    if (!$seller_id) {
        echo json_encode([
            "status" => "error",
            "message" => "Seller ID required"
        ]);
        exit;
    }

    $seller_id = intval($seller_id);

    // Prepare SQL with optional filters
    $sql = "SELECT
                o.id AS order_id,
                o.buyer_id,
                o.discount,
                b.username AS buyer_name,
                b.email AS buyer_email,
                o.payment_method,
                o.total_amount,
                o.status,
                o.created_at AS order_date,
                ba.recipient_name,
                ba.phone_number,
                ba.barangay,
                ba.street_address,
                ba.is_default,
                p.product_name,
                p.product_description,
                p.category,
                p.id AS product_id,
                p.main_image_url,
                p.seller_id,
                oi.quantity,
                oi.unit_price,
                oi.total_price,
                oi.variation_id,
                pv.options_json_value AS variation,
                pv.sku,
                pv.image_urls
            FROM
                orders o
            INNER JOIN
                order_items oi ON o.id = oi.order_id
            INNER JOIN
                products p ON oi.product_id = p.id
            LEFT JOIN
                product_variants pv ON oi.variation_id = pv.id
            INNER JOIN
                buyers b ON o.buyer_id = b.id
            INNER JOIN
                buyer_addresses ba ON o.address_id = ba.id
            WHERE
                p.seller_id = :seller_id";

    // Add status filter if provided
    if ($status && $status !== 'all') {
        $sql .= " AND o.status = :status";
    }

    $sql .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";

    // Prepare and execute main query
    $stmt = $conn->prepare($sql);

    // Bind parameters - use bindValue for LIMIT and OFFSET as they need to be treated as integers
    $stmt->bindValue(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    if ($status && $status !== 'all') {
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orders = [];

    foreach ($rows as $row) {
        // Format the order data
        $order = [
            "order_id" => (int)$row['order_id'],
            "buyer_id" => (int)$row['buyer_id'],
            "buyer_name" => $row['buyer_name'],
            "buyer_email" => $row['buyer_email'],
            "payment_method" => $row['payment_method'],
            "total_amount" => (float)$row['total_amount'],
            "status" => $row['status'],
            "order_date" => $row['order_date'],
            "shipping_info" => [
                "recipient_name" => $row['recipient_name'],
                "phone_number" => $row['phone_number'],
                "barangay" => $row['barangay'],
                "street_address" => $row['street_address'],
                "is_default" => (bool)$row['is_default']
            ],
            "product_info" => [
                "product_id" => (int)$row['product_id'],
                "product_name" => $row['product_name'],
                "product_description" => $row['product_description'],
                "discount" => (float)$row['discount'],
                "category" => $row['category'],
                "quantity" => (int)$row['quantity'],
                "unit_price" => (float)$row['unit_price'],
                "total_price" => (float)$row['total_price'],
                "variation_id" => $row['variation_id'] !== null ? (int)$row['variation_id'] : null,
                "variation" => $row['variation'],
                "sku" => $row['sku'],
                "image_urls" => $row['image_urls'],
                "main_image_url" => $row['main_image_url']
            ],
            "seller_id" => (int)$row['seller_id']
        ];
        $orders[] = $order;
    }

    // Get total count for pagination
    $count_sql = "SELECT COUNT(DISTINCT o.id) as total
                  FROM orders o
                  INNER JOIN order_items oi ON o.id = oi.order_id
                  INNER JOIN products p ON oi.product_id = p.id
                  WHERE p.seller_id = :seller_id";

    if ($status && $status !== 'all') {
        $count_sql .= " AND o.status = :status";
    }

    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bindValue(':seller_id', $seller_id, PDO::PARAM_INT);

    if ($status && $status !== 'all') {
        $count_stmt->bindValue(':status', $status, PDO::PARAM_STR);
    }

    $count_stmt->execute();
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        "status" => "success",
        "message" => "Orders retrieved successfully",
        "seller_id" => $seller_id,
        "total_orders" => (int)$total_count,
        "current_page" => $limit > 0 ? ($offset / $limit) + 1 : 1,
        "total_pages" => $limit > 0 ? ceil($total_count / $limit) : 1,
        "orders" => $orders
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>