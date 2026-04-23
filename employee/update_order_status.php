<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

// Function to call sendNotification.php internally
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Don't block the main request
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

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

// Function to generate professional status messages
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

// Function to get notification title
function getStatusTitle($status) {
    $titles = [
        "packed" => "Order Packed & Ready",
        "shipped" => "Order On The Way",
        "delivered" => "Order Delivered",
        "assigned" => "Rider Assigned",
        "complete" => "Order Complete",
        "cancelled" => "Order Cancelled"
    ];
    
    return $titles[$status] ?? "Order Status Update";
}

// Statuses that should trigger notifications
function shouldSendNotification($status) {
    $notify_statuses = ["packed", "shipped", "delivered", "assigned", "complete", "cancelled"];
    return in_array($status, $notify_statuses);
}

try {
    // Get inputs
    $buyer_id = $_POST['buyer_id'] ?? $_GET['buyer_id'] ?? null;
    $order_id = $_POST['order_id'] ?? $_GET['order_id'] ?? null;
    $status   = $_POST['status'] ?? $_GET['status'] ?? null;

    if (!$buyer_id || !$order_id || !$status) {
        echo json_encode([
            "status" => "error",
            "message" => "buyer_id, order_id and status are required"
        ]);
        exit;
    }

    // Allowed statuses (match ENUM)
    $allowed_statuses = [
        "pending",
        "packed",
        "shipped",
        "delivered",
        "locked",
        "assigned",
        "reassigned",
        "complete",
        "cancelled"
    ];

    if (!in_array($status, $allowed_statuses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid status value"
        ]);
        exit;
    }

    $buyer_id = intval($buyer_id);
    $order_id = intval($order_id);

    // Update query
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
        
        $notification_saved = false;
        $notification_sent = false;
        
        // Send notification for specific statuses
        if (shouldSendNotification($status)) {
            $title = getStatusTitle($status);
            $message = getStatusMessage($order_id, $status);
            
            // Save to database
            $notification_saved = saveNotification($conn, $buyer_id, $title, $message);
            
            // Send push notification
            $notification_sent = sendPushIfTokenExists($conn, $buyer_id, $title, $message);
        }
        
        echo json_encode([
            "status" => "success",
            "message" => "Order status updated successfully",
            "order_id" => $order_id,
            "new_status" => $status,
            "notification_saved" => $notification_saved,
            "notification_sent" => $notification_sent
        ]);
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Order not found or status already the same"
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

$conn = null; // Close PDO connection
?>