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
        "packed" => "Great news! Your order #$order_id has been carefully packed and is ready for shipping. You'll receive tracking information once it's on the way.",
        "shipped" => "Your order #$order_id is on the way! It has been shipped and is now waiting for rider assignment. Track your delivery in real-time.",
        "delivered" => "Your order #$order_id has been delivered successfully! Thank you for shopping with DaguitZone. We hope you love your purchase!",
        "assigned" => "A rider has been assigned to your order #$order_id. They will pick up your package shortly.",
        "complete" => "Order #$order_id has been completed. Thank you for choosing DaguitZone! Please rate your experience.",
        "cancelled" => "Order #$order_id has been cancelled. If you have any questions, please contact our support team."
    ];
    
    return $messages[$status] ?? "Your order #$order_id status has been updated to: $status";
}

function getStatusTitle($status) {
    $titles = [
        "packed" => "📦 Order Packed & Ready",
        "shipped" => "🚚 Order On The Way",
        "delivered" => "✅ Order Delivered",
        "assigned" => "🛵 Rider Assigned",
        "complete" => "🎉 Order Complete",
        "cancelled" => "❌ Order Cancelled"
    ];
    
    return $titles[$status] ?? "📋 Order Status Update";
}

function shouldSendNotification($status) {
    $notify_statuses = ["packed", "shipped", "delivered", "assigned", "complete", "cancelled"];
    return in_array($status, $notify_statuses);
}

try {
    $buyer_id  = $_POST['buyer_id'] ?? $_GET['buyer_id'] ?? null;
    $order_id  = $_POST['order_id'] ?? $_GET['order_id'] ?? null;
    $status    = $_POST['status'] ?? $_GET['status'] ?? null;
    $seller_id = $_POST['seller_id'] ?? $_GET['seller_id'] ?? null;

    if (!$buyer_id || !$order_id || !$status) {
        echo json_encode([
            "status" => "error",
            "message" => "buyer_id, order_id and status are required"
        ]);
        exit;
    }

    $allowed_statuses = [
        "pending", "packed", "shipped", "delivered", "locked",
        "assigned", "reassigned", "complete", "cancelled"
    ];

    if (!in_array($status, $allowed_statuses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid status value"
        ]);
        exit;
    }

    $buyer_id  = intval($buyer_id);
    $order_id  = intval($order_id);
    $seller_id = $seller_id ? intval($seller_id) : null;

    // ✅ SHIPPED: Update order_items, then promote if all ready
    if ($status === 'shipped') {
        if (!$seller_id) {
            echo json_encode([
                "status" => "error",
                "message" => "seller_id is required when status is 'shipped'"
            ]);
            exit;
        }

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

        if ($allReady) {
            // Promote order to 'shipped'
            $promoteSql = "UPDATE orders
                           SET status = 'shipped', updated_at = NOW()
                           WHERE id = :order_id";

            $promoteStmt = $conn->prepare($promoteSql);
            $promoteStmt->execute([':order_id' => $order_id]);

            // Send notification
            $notification_sent = false;
            if (shouldSendNotification('shipped')) {
                $title = getStatusTitle('shipped');
                $message = getStatusMessage($order_id, 'shipped');
                $notification_result = sendPushNotification($buyer_id, $title, $message);
                $notification_sent = $notification_result['success'] ?? false;
            }

            echo json_encode([
                "status" => "success",
                "message" => "All items ready. Order has been marked as shipped.",
                "order_id" => $order_id,
                "new_status" => "shipped",
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

    } else {
        // ✅ PACKED and other statuses: Direct update
        $sql = "UPDATE orders
                SET status = :status, updated_at = NOW()
                WHERE id = :order_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':order_id' => $order_id
        ]);

        $rowCount = $stmt->rowCount();

        if ($rowCount > 0) {
            $notification_sent = false;

            if (shouldSendNotification($status)) {
                $title = getStatusTitle($status);
                $message = getStatusMessage($order_id, $status);
                $notification_result = sendPushNotification($buyer_id, $title, $message);
                $notification_sent = $notification_result['success'] ?? false;
            }

            echo json_encode([
                "status" => "success",
                "message" => "Order status updated successfully",
                "order_id" => $order_id,
                "new_status" => $status,
                "notification_sent" => $notification_sent
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Order not found or status already the same"
            ]);
        }
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