<?php
header('Content-Type: application/json');

require '/var/www/html/connection/db_connection.php';

try {
    // Handle both JSON and form-data input
    $order_id = 0;
    $status = '';
    $rider_id = 0;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
        $data = json_decode(file_get_contents("php://input"), true);
        $order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
        $status = isset($data['status']) ? $data['status'] : '';
        $rider_id = isset($data['rider_id']) ? intval($data['rider_id']) : 0;
    } else {
        // Regular form POST
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
    }

    if (!$order_id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    // Allowed statuses (matching your CHECK constraint)
    $allowed_status = [
        'pending', 'pending_payment', 'packed', 'ready_for_pickup', 
        'shipped', 'delivered', 'locked', 'assigned', 'reassigned', 
        'complete', 'cancelled'
    ];
    
    if (!in_array($status, $allowed_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }

    // ✅ Check rider status BEFORE locking
    if ($status === 'locked') {
        
        // Require rider_id when locking
        if (!$rider_id) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Rider ID is required to lock an order'
            ]);
            exit;
        }

        // Check rider's current status
        $riderStmt = $conn->prepare("
            SELECT status, verification_status 
            FROM riders 
            WHERE id = :rider_id
        ");
        $riderStmt->execute([':rider_id' => $rider_id]);
        $rider = $riderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$rider) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Rider not found'
            ]);
            exit;
        }

        // ✅ Check if rider is verified
        if ($rider['verification_status'] !== 'complete') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Rider account is not fully verified'
            ]);
            exit;
        }

        // ✅ Check if rider is available (not busy or offline)
        if ($rider['status'] === 'busy') {
            echo json_encode([
                'status' => 'error',
                'message' => 'You are currently on a delivery. Complete it before accepting new orders.'
            ]);
            exit;
        }

        if ($rider['status'] === 'offline') {
            echo json_encode([
                'status' => 'error',
                'message' => 'You are offline. Please go online to accept orders.'
            ]);
            exit;
        }

        // ✅ Check if order is already locked (check order_deliveries table)
        $orderCheckStmt = $conn->prepare("
            SELECT o.status, o.locked_at, od.rider_id as locked_by
            FROM orders o
            LEFT JOIN order_deliveries od ON o.id = od.order_id AND od.delivery_status = 'locked'
            WHERE o.id = :order_id
        ");
        $orderCheckStmt->execute([':order_id' => $order_id]);
        $order = $orderCheckStmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Order not found'
            ]);
            exit;
        }

        // Check if order is already locked
        if ($order['status'] === 'locked') {
            // Check if lock is expired (5 minute lock expiry)
            $lockExpiryTime = 5; // minutes
            $lockedAt = strtotime($order['locked_at']);
            $currentTime = time();
            $lockAge = ($currentTime - $lockedAt) / 60; // in minutes

            if ($lockAge < $lockExpiryTime && $order['locked_by'] != $rider_id) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'This order is already locked by another rider'
                ]);
                exit;
            }
        }

        // ✅ All checks passed - Lock the order
        $conn->beginTransaction();

        try {
            // Update order status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = :status, 
                    locked_at = NOW(),
                    updated_at = NOW()
                WHERE id = :order_id
            ");
            $success = $stmt->execute([
                ':status' => $status,
                ':order_id' => $order_id
            ]);

            // Create or update order_deliveries record
            $deliveryStmt = $conn->prepare("
                INSERT INTO order_deliveries (order_id, rider_id, delivery_status, locked_at)
                VALUES (:order_id, :rider_id, 'locked', NOW())
                ON CONFLICT (order_id) DO UPDATE 
                SET rider_id = :rider_id, 
                    delivery_status = 'locked',
                    locked_at = NOW()
            ");
            $deliveryStmt->execute([
                ':order_id' => $order_id,
                ':rider_id' => $rider_id
            ]);

            // ✅ Update rider status to 'busy'
            $updateRiderStmt = $conn->prepare("
                UPDATE riders 
                SET status = 'busy' 
                WHERE id = :rider_id
            ");
            $updateRiderStmt->execute([':rider_id' => $rider_id]);

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => "Order $order_id locked successfully",
                'rider_id' => $rider_id
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }

    } elseif ($status === 'assigned') {
        // ✅ Handle assigned status
        if (!$rider_id) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Rider ID is required to assign an order'
            ]);
            exit;
        }

        $conn->beginTransaction();

        try {
            // Update order status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = :status, 
                    updated_at = NOW()
                WHERE id = :order_id
            ");
            $stmt->execute([
                ':status' => $status,
                ':order_id' => $order_id
            ]);

            // Update order_deliveries status
            $deliveryStmt = $conn->prepare("
                UPDATE order_deliveries 
                SET delivery_status = 'assigned',
                    assigned_at = NOW()
                WHERE order_id = :order_id AND rider_id = :rider_id
            ");
            $deliveryStmt->execute([
                ':order_id' => $order_id,
                ':rider_id' => $rider_id
            ]);

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => "Order $order_id assigned successfully"
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }

    } else {
        // For other status updates
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = :status, 
                updated_at = NOW() 
            WHERE id = :order_id
        ");
        $success = $stmt->execute([
            ':status' => $status,
            ':order_id' => $order_id
        ]);

        // ✅ If order is delivered or cancelled, free up the rider
        if ($status === 'delivered' || $status === 'cancelled') {
            // Get the rider from order_deliveries
            $orderInfoStmt = $conn->prepare("
                SELECT rider_id 
                FROM order_deliveries 
                WHERE order_id = :order_id AND delivery_status IN ('assigned', 'picked_up')
            ");
            $orderInfoStmt->execute([':order_id' => $order_id]);
            $orderInfo = $orderInfoStmt->fetch(PDO::FETCH_ASSOC);

            if ($orderInfo && $orderInfo['rider_id']) {
                // Set rider back to online
                $freeRiderStmt = $conn->prepare("
                    UPDATE riders 
                    SET status = 'online' 
                    WHERE id = :rider_id
                ");
                $freeRiderStmt->execute([':rider_id' => $orderInfo['rider_id']]);

                // Update delivery status
                $updateDeliveryStmt = $conn->prepare("
                    UPDATE order_deliveries 
                    SET delivery_status = :status,
                        delivered_at = CASE WHEN :status = 'delivered' THEN NOW() ELSE delivered_at END
                    WHERE order_id = :order_id
                ");
                $updateDeliveryStmt->execute([
                    ':status' => $status,
                    ':order_id' => $order_id
                ]);
            }
        }

        if ($success) {
            echo json_encode([
                'status' => 'success',
                'message' => "Order $order_id updated to $status"
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update order'
            ]);
        }
    }

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>