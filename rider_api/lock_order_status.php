<?php
header('Content-Type: application/json');

require '/var/www/html/connection/db_connection.php';

// ✅ Enable error logging
error_log("=== Update Order Status API Called ===");

// Function to save notification directly to database
function saveNotification($conn, $user_id, $title, $message) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, notif_message, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $title, $message]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to save notification: " . $e->getMessage());
        return false;
    }
}

// Function to get user's FCM token and send push
function sendPushIfTokenExists($conn, $user_id, $title, $message) {
    try {
        // Get user's FCM token
        $stmt = $conn->prepare("SELECT fcm_token FROM user_tokens WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenRow || empty($tokenRow['fcm_token'])) {
            return false;
        }
        
        $fcmToken = $tokenRow['fcm_token'];
        
        // Load Firebase credentials
        $firebaseJson = getenv('FIREBASE_CREDENTIALS');
        if (!$firebaseJson) {
            error_log("FIREBASE_CREDENTIALS not found");
            return false;
        }
        
        $credentialsArray = json_decode($firebaseJson, true);
        $projectId = $credentialsArray['project_id'];
        
        // Generate OAuth2 Access Token
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $creds = new Google\Auth\Credentials\ServiceAccountCredentials($scopes, $credentialsArray);
        $tokenData = $creds->fetchAuthToken();
        $accessToken = $tokenData['access_token'];
        
        // Send to Firebase
        $payload = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
        
    } catch (Exception $e) {
        error_log("Push notification error: " . $e->getMessage());
        return false;
    }
}

// Get status message
function getStatusMessage($order_id, $status) {
    $messages = [
        "assigned" => "Great news! A rider has been assigned to your order #$order_id. They will pick up your package shortly. Track your delivery in real-time!",
        "packed" => "Great news! Your order #$order_id has been carefully packed and is ready for shipping. You'll receive tracking information once it's on the way.",
        "shipped" => "Your order #$order_id is on the way! It has been shipped and is now waiting for rider assignment. Track your delivery in real-time.",
        "delivered" => "Your order #$order_id has been delivered successfully! Thank you for shopping with DaguitZone. We hope you love your purchase!",
        "complete" => "Order #$order_id has been completed. Thank you for choosing DaguitZone! Please rate your experience.",
        "cancelled" => "Order #$order_id has been cancelled. If you have any questions, please contact our support team."
    ];
    
    return $messages[$status] ?? "Your order #$order_id status has been updated to: $status";
}

// Get notification title (without emojis)
function getStatusTitle($status) {
    $titles = [
        "assigned" => "Rider Assigned",
        "packed" => "Order Packed & Ready",
        "shipped" => "Order On The Way",
        "delivered" => "Order Delivered",
        "complete" => "Order Complete",
        "cancelled" => "Order Cancelled"
    ];
    
    return $titles[$status] ?? "Order Status Update";
}

// Check if status should trigger notification
function shouldSendNotification($status) {
    $notify_statuses = ["assigned", "packed", "shipped", "delivered", "complete", "cancelled"];
    return in_array($status, $notify_statuses);
}

// Send order notification (save to DB + push)
function sendOrderNotification($conn, $order_id, $status) {
    try {
        // Get buyer_id from orders table
        $stmt = $conn->prepare("SELECT buyer_id FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order || !isset($order['buyer_id'])) {
            error_log("Notification: Could not find buyer_id for order $order_id");
            return ['saved' => false, 'sent' => false];
        }
        
        $buyer_id = $order['buyer_id'];
        
        if (shouldSendNotification($status)) {
            $title = getStatusTitle($status);
            $message = getStatusMessage($order_id, $status);
            
            // Save to database
            $saved = saveNotification($conn, $buyer_id, $title, $message);
            error_log("Notification saved for order $order_id, status $status: " . ($saved ? 'Success' : 'Failed'));
            
            // Send push notification
            $sent = sendPushIfTokenExists($conn, $buyer_id, $title, $message);
            error_log("Push sent for order $order_id, status $status: " . ($sent ? 'Success' : 'Failed'));
            
            return ['saved' => $saved, 'sent' => $sent];
        }
        
        return ['saved' => false, 'sent' => false];
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false];
    }
}

try {
    // Handle both JSON and form-data input
    $order_id = 0;
    $status = '';
    $rider_id = 0;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
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

    // ✅ Log received parameters
    error_log("Received - order_id: $order_id, status: $status, rider_id: $rider_id");

    if (!$order_id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    // Allowed statuses
    $allowed_status = ['pending', 'packed', 'shipped', 'delivered', 'locked', 'assigned', 'reassinged', 'complete', 'cancelled'];
    if (!in_array($status, $allowed_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }

    // Check rider status BEFORE locking
    if ($status === 'locked') {
        
        error_log("Processing LOCK request for rider_id: $rider_id");
        
        // Require rider_id when locking
        if (!$rider_id) {
            error_log("ERROR: Missing rider_id");
            echo json_encode([
                'status' => 'error', 
                'message' => 'Rider ID is required to lock an order'
            ]);
            exit;
        }

        // Check rider's current status
        $riderStmt = $conn->prepare("SELECT id, status FROM riders WHERE id = :rider_id");
        $riderStmt->execute([':rider_id' => $rider_id]);
        $rider = $riderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$rider) {
            error_log("ERROR: Rider not found - ID: $rider_id");
            echo json_encode([
                'status' => 'error',
                'message' => 'Rider not found'
            ]);
            exit;
        }

        // ✅ Log rider's current status
        error_log("Rider found - ID: {$rider['id']}, Status: {$rider['status']}");

        // ✅ Check if rider is delivering
        if ($rider['status'] === 'delivering') {
            error_log("BLOCKED: Rider is delivering");
            echo json_encode([
                'status' => 'error',
                'message' => 'You are currently on a delivery. Complete it before accepting new orders.'
            ]);
            exit;
        }

        // ✅ Check if rider is offline
        if ($rider['status'] === 'offline') {
            error_log("BLOCKED: Rider is offline");
            echo json_encode([
                'status' => 'error',
                'message' => 'You are offline. Please go online to accept orders.'
            ]);
            exit;
        }

        error_log("Rider status OK - Proceeding to lock order");
        
        // All good - lock the order
        $stmt = $conn->prepare("UPDATE orders SET status = ?, locked_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$status, $order_id]);
        
    } else {
        // For other statuses, just update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $success = $stmt->execute([$status, $order_id]);
    }

    if ($success && $stmt->rowCount() > 0) {
        
        // Send notification if applicable
        $notification_result = ['saved' => false, 'sent' => false];
        if (shouldSendNotification($status)) {
            $notification_result = sendOrderNotification($conn, $order_id, $status);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => "Order $order_id updated to $status",
            'notification_saved' => $notification_result['saved'],
            'notification_sent' => $notification_result['sent']
        ]);
        
    } elseif ($success && $stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "Order not found or status unchanged"
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update order'
        ]);
    }

} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Exception: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>