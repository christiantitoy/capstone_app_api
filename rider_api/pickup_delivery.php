<?php
header("Content-Type: application/json");

require_once "/var/www/html/connection/db_connection.php"; // Updated to match your path

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

// Get pickup notification title
function getPickupTitle() {
    return "Order Picked Up";
}

// Get pickup notification message
function getPickupMessage($order_id) {
    return "Great news! Your order #$order_id has been picked up by our rider and is now on its way to you. Track your delivery in real-time!";
}

// Send pickup notification (save to DB + push)
function sendPickupNotification($conn, $delivery_id) {
    try {
        // Get order_id and buyer_id from order_deliveries and orders tables
        $sql = "
            SELECT o.id as order_id, o.buyer_id 
            FROM order_deliveries od
            JOIN orders o ON od.order_id = o.id
            WHERE od.id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$delivery_id]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$delivery || !isset($delivery['buyer_id'])) {
            error_log("Pickup Notification: Could not find buyer_id for delivery $delivery_id");
            return ['saved' => false, 'sent' => false];
        }
        
        $buyer_id = $delivery['buyer_id'];
        $order_id = $delivery['order_id'];
        
        $title = getPickupTitle();
        $message = getPickupMessage($order_id);
        
        // Save to database
        $saved = saveNotification($conn, $buyer_id, $title, $message);
        error_log("Pickup notification saved for order $order_id: " . ($saved ? 'Success' : 'Failed'));
        
        // Send push notification
        $sent = sendPushIfTokenExists($conn, $buyer_id, $title, $message);
        error_log("Pickup push sent for order $order_id: " . ($sent ? 'Success' : 'Failed'));
        
        return ['saved' => $saved, 'sent' => $sent];
        
    } catch (Exception $e) {
        error_log("Pickup Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false];
    }
}

try {
    // Handle both JSON and form-data input
    $delivery_id = null;

    // Check if it's JSON content type
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents("php://input"), true);
        $delivery_id = $data['delivery_id'] ?? null;
    } else {
        // Regular form POST
        $delivery_id = $_POST['delivery_id'] ?? null;
    }

    if (!$delivery_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing delivery_id"
        ]);
        exit;
    }

    $sql = "
        UPDATE order_deliveries
        SET
            status = 'picked_up',
            picked_up_at = NOW()
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([$delivery_id]);

    if ($success) {
        // Check if any row was actually updated
        if ($stmt->rowCount() > 0) {
            
            // Send pickup notification (save + push)
            $notification_result = sendPickupNotification($conn, $delivery_id);
            
            echo json_encode([
                "success" => true,
                "message" => "Order picked up successfully",
                "notification_saved" => $notification_result['saved'],
                "notification_sent" => $notification_result['sent']
            ]);
            
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Delivery not found or already picked up"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update status"
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

// No need to explicitly close the connection with PDO
?>