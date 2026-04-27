<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

function sendPushNotification($user_id, $title, $message) {
    $url = 'https://capstone-app-api-r1ux.onrender.com/connection/notif/sendNotification.php';
    
    $data = json_encode([
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getStatusMessage($order_id, $status) {
    $messages = [
        "shipped" => "Your order #$order_id is on the way! It has been shipped and is now waiting for rider assignment. Track your delivery in real-time.",
    ];
    return $messages[$status] ?? "Your order #$order_id status has been updated to: $status";
}

function getStatusTitle($status) {
    $titles = [
        "shipped" => "🚚 Order On The Way",
    ];
    return $titles[$status] ?? "📋 Order Status Update";
}

try {
    $seller_id = $_POST['seller_id'] ?? $_GET['seller_id'] ?? null;
    $order_id  = $_POST['order_id'] ?? $_GET['order_id'] ?? null;

    if (!$seller_id || !$order_id) {
        echo json_encode([
            "status" => "error",
            "message" => "seller_id and order_id are required"
        ]);
        exit;
    }

    $seller_id = intval($seller_id);
    $order_id  = intval($order_id);

    // Update only this seller's order_items
    $updateSql = "UPDATE order_items oi
                  SET is_shipped = true
                  FROM items i
                  WHERE oi.product_id = i.id
                  AND oi.order_id = :order_id
                  AND i.seller_id = :seller_id
                  AND oi.is_shipped IS DISTINCT FROM true";

    $stmt = $conn->prepare($updateSql);
    $stmt->execute([
        ':order_id' => $order_id,
        ':seller_id' => $seller_id
    ]);

    $updatedRows = $stmt->rowCount();

    if ($updatedRows === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No items found for this seller in this order, or items already marked ready"
        ]);
        exit;
    }

    // Check if ALL items are ready
    $checkSql = "SELECT COUNT(*) as pending_count
                 FROM order_items
                 WHERE order_id = :order_id
                 AND is_shipped IS DISTINCT FROM true";

    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([':order_id' => $order_id]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $allReady = ($result['pending_count'] == 0);
    $notification_sent = false;

    if ($allReady) {
        // Promote order + get buyer_id
        $promoteSql = "UPDATE orders
                       SET status = 'shipped', updated_at = NOW()
                       WHERE id = :order_id
                       RETURNING buyer_id";

        $promoteStmt = $conn->prepare($promoteSql);
        $promoteStmt->execute([':order_id' => $order_id]);
        $orderData = $promoteStmt->fetch(PDO::FETCH_ASSOC);

        // Send notification
        if ($orderData && $orderData['buyer_id']) {
            $title = getStatusTitle('shipped');
            $message = getStatusMessage($order_id, 'shipped');
            $notification_result = sendPushNotification($orderData['buyer_id'], $title, $message);
            $notification_sent = $notification_result['success'] ?? false;
        }

        echo json_encode([
            "status" => "success",
            "message" => "All items ready. Order has been marked as shipped.",
            "order_id" => $order_id,
            "order_status" => "shipped",
            "notification_sent" => $notification_sent
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "message" => "Seller items marked as ready. Waiting for other sellers.",
            "order_id" => $order_id,
            "items_updated" => $updatedRows,
            "pending_items" => (int)$result['pending_count']
        ]);
    }

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

$conn = null;
?>