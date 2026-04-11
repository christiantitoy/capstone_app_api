<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php";

try {
    // Handle both JSON and form-data input for GET parameters
    $rider_id = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $rider_id = $_GET['rider_id'] ?? null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if it's JSON content type
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $data = json_decode(file_get_contents("php://input"), true);
            $rider_id = $data['rider_id'] ?? null;
        } else {
            $rider_id = $_POST['rider_id'] ?? null;
        }
    }

    if (!$rider_id) {
        echo json_encode(["success" => false, "message" => "Missing rider_id"]);
        exit;
    }

    $sql = "
        SELECT
            od.id AS delivery_id,
            od.order_id,
            od.status AS delivery_status,
            od.assigned_at,
            od.picked_up_at,
            od.created_at AS delivery_created_at,

            -- Order Info
            o.*,

            -- Buyer Info
            b.username AS buyer_username,
            b.email AS buyer_email,
            b.avatar_url AS buyer_avatar,

            -- Buyer Address Info (Updated fields)
            ba.recipient_name,
            ba.phone_number,
            ba.full_address,
            ba.gps_location,
            ba.is_default,

            -- Seller Info (from the first product in the order)
            sp.id AS seller_profile_id,
            sp.buyer_id AS seller_buyer_id,
            sp.shop_name,
            sp.fullname AS seller_fullname,
            sp.business_address AS seller_business_address,
            sp.phone_number AS seller_phone,
            sp.shop_category,
            sp.business_type,
            sp.is_approved AS seller_is_approved

        FROM order_deliveries od
        INNER JOIN orders o ON o.id = od.order_id
        INNER JOIN buyers b ON b.id = o.buyer_id
        INNER JOIN buyer_addresses ba ON ba.id = o.address_id
        LEFT JOIN (
            SELECT DISTINCT oi.order_id, p.seller_id
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE p.seller_id IS NOT NULL
        ) AS order_sellers ON order_sellers.order_id = o.id
        LEFT JOIN seller_profiles sp ON sp.id = order_sellers.seller_id
        WHERE od.rider_id = ?
          AND od.status IN ('assigned','picked_up','delivering')
        ORDER BY od.created_at DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$rider_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Get order items
        $order_id = $row['order_id'];
        $items_sql = "
            SELECT oi.*,
                   p.product_name, p.category, p.main_image_url,
                   sp.shop_name AS seller_shop_name
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            LEFT JOIN seller_profiles sp ON sp.id = p.seller_id
            WHERE oi.order_id = ?
        ";

        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add order items to the row
        $row['order_items'] = $order_items;

        echo json_encode([
            "success" => true,
            "data" => $row
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "data" => null,
            "message" => "No active delivery"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>