<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once "/var/www/html/connection/db_connection.php";
require_once '/var/www/html/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Exception\ApiError;

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

// Get delivery completion message for buyer
function getDeliveredMessage($order_id) {
    return "Your order #$order_id has been delivered successfully! Thank you for shopping with DaguitZone. We hope you love your purchase!";
}

// Send delivery notification to buyer (save + push)
function sendDeliveryNotification($conn, $order_id) {
    try {
        // Get buyer_id from orders table
        $stmt = $conn->prepare("SELECT buyer_id FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order || !isset($order['buyer_id'])) {
            error_log("Delivery Notification: Could not find buyer_id for order $order_id");
            return ['saved' => false, 'sent' => false];
        }
        
        $buyer_id = $order['buyer_id'];
        $title = "Order Delivered";
        $message = getDeliveredMessage($order_id);
        
        // Save to database
        $saved = saveNotification($conn, $buyer_id, $title, $message);
        error_log("Delivery notification saved for order $order_id: " . ($saved ? 'Success' : 'Failed'));
        
        // Send push notification
        $sent = sendPushIfTokenExists($conn, $buyer_id, $title, $message);
        error_log("Delivery push sent for order $order_id: " . ($sent ? 'Success' : 'Failed'));
        
        return ['saved' => $saved, 'sent' => $sent];
        
    } catch (Exception $e) {
        error_log("Delivery Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false];
    }
}

// Get seller notification message
function getSellerMessage($order_id) {
    return "Great news! Order #$order_id has been successfully delivered to the buyer. The earnings have been added to your account.";
}

// Send notification to seller (save + push)
function sendSellerNotification($conn, $order_id) {
    try {
        // Get seller_id from order_items and items tables
        $sql = "
            SELECT DISTINCT i.seller_id 
            FROM order_items oi
            JOIN items i ON oi.product_id = i.id
            WHERE oi.order_id = ?
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$order_id]);
        $seller = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$seller || !isset($seller['seller_id'])) {
            error_log("Seller Notification: Could not find seller_id for order $order_id");
            return ['saved' => false, 'sent' => false];
        }
        
        $seller_id = $seller['seller_id'];
        $title = "Order Completed";
        $message = getSellerMessage($order_id);
        
        // Save to database
        $saved = saveNotification($conn, $seller_id, $title, $message);
        error_log("Seller notification saved for order $order_id: " . ($saved ? 'Success' : 'Failed'));
        
        // Send push notification
        $sent = sendPushIfTokenExists($conn, $seller_id, $title, $message);
        error_log("Seller push sent for order $order_id: " . ($sent ? 'Success' : 'Failed'));
        
        return ['saved' => $saved, 'sent' => $sent];
        
    } catch (Exception $e) {
        error_log("Seller Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false];
    }
}

try {
    // Configure Cloudinary
    Configuration::instance(getenv('CLOUDINARY_URL'));
    
    if (!getenv('CLOUDINARY_URL')) {
        throw new Exception('CLOUDINARY_URL environment variable is not set.');
    }

    $delivery_id = null;
    $rider_id = null;
    $order_id = null;
    $proof_image_path = null;

    // Handle multipart form data (from Android)
    $delivery_id = $_POST['delivery_id'] ?? null;
    $rider_id = $_POST['rider_id'] ?? null;
    $order_id = $_POST['order_id'] ?? null;

    if (!$delivery_id || !$rider_id || !$order_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing delivery_id, rider_id, or order_id"
        ]);
        exit;
    }

    // Check if image was uploaded
    if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "success" => false,
            "message" => "Proof image is required"
        ]);
        exit;
    }

    $file = $_FILES['proof_image'];
    
    // Validate file extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid file type. Allowed: " . implode(', ', $allowedExtensions)
        ]);
        exit;
    }

    // Upload to Cloudinary
    try {
        $uploadApi = new UploadApi();
        $folder = 'capstone_app_images/delivery_proofs';
        $publicId = 'delivery_' . $delivery_id . '_' . uniqid();
        
        $result = $uploadApi->upload($file['tmp_name'], [
            'folder' => $folder,
            'public_id' => $publicId,
            'overwrite' => true,
            'resource_type' => 'image',
            'quality' => 'auto',
            'fetch_format' => 'auto',
            'tags' => ['delivery_proof', 'order_' . $order_id, 'rider_' . $rider_id]
        ]);
        
        $proof_image_path = $result['secure_url'];
        
    } catch (ApiError $e) {
        echo json_encode([
            "success" => false,
            "message" => "Cloudinary upload failed: " . $e->getMessage()
        ]);
        exit;
    }

    // Begin database transaction
    $conn->beginTransaction();

    try {
        // 1️⃣ Update order_deliveries to completed
        $sql1 = "
            UPDATE order_deliveries
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ?
        ";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$delivery_id]);

        if ($stmt1->rowCount() === 0) {
            throw new Exception("Delivery not found or already completed");
        }

        // 2️⃣ Insert into delivery_proofs table
        $sql2 = "
            INSERT INTO delivery_proofs (delivery_id, order_id, rider_id, proof_image_path)
            VALUES (?, ?, ?, ?)
        ";
        
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$delivery_id, $order_id, $rider_id, $proof_image_path]);

        // 3️⃣ Update rider status to online
        $sql3 = "
            UPDATE riders
            SET status = 'online'
            WHERE id = ?
        ";

        $stmt3 = $conn->prepare($sql3);
        $stmt3->execute([$rider_id]);

        if ($stmt3->rowCount() === 0) {
            throw new Exception("Rider not found");
        }

        // 4️⃣ Update orders table to delivered AND get shipping_fee & total_amount
        $sql4 = "
            UPDATE orders
            SET status = 'delivered'
            WHERE id = ?
            RETURNING shipping_fee, total_amount
        ";

        $stmt4 = $conn->prepare($sql4);
        $stmt4->execute([$order_id]);

        if ($stmt4->rowCount() === 0) {
            throw new Exception("Order not found");
        }
        
        // Fetch the returned values
        $orderData = $stmt4->fetch(PDO::FETCH_ASSOC);
        $shipping_fee = $orderData['shipping_fee'];
        $total_amount = $orderData['total_amount'];

        // 5️⃣ Insert into rider_earnings table
        $sql5 = "
            INSERT INTO rider_earnings (rider_id, order_id, delivery_id, shipping_fee, total_amount)
            VALUES (?, ?, ?, ?, ?)
        ";
        
        $stmt5 = $conn->prepare($sql5);
        $stmt5->execute([$rider_id, $order_id, $delivery_id, $shipping_fee, $total_amount]);

        // 6️⃣ Insert into sold_items table for each order item
        $sql6 = "
            INSERT INTO sold_items (order_deliveries_id, order_items_id, orders_id, created_at)
            SELECT ?, oi.id, ?, NOW()
            FROM order_items oi
            WHERE oi.order_id = ?
        ";

        $stmt6 = $conn->prepare($sql6);
        $stmt6->execute([$delivery_id, $order_id, $order_id]);
        $itemsInserted = $stmt6->rowCount();

        // 7️⃣ Increment sold count in items table for each product
        $sql7 = "
            UPDATE items i
            SET sold = sold + oi.quantity
            FROM order_items oi
            WHERE oi.order_id = ?
            AND i.id = oi.product_id
        ";

        $stmt7 = $conn->prepare($sql7);
        $stmt7->execute([$order_id]);
        $productsUpdated = $stmt7->rowCount();

        $conn->commit();

        // Send notifications after successful commit
        $buyer_result = sendDeliveryNotification($conn, $order_id);
        $seller_result = sendSellerNotification($conn, $order_id);

        echo json_encode([
            "success" => true,
            "message" => "Order marked as delivered successfully",
            "proof_image_url" => $proof_image_path,
            "earnings_recorded" => [
                "shipping_fee" => $shipping_fee,
                "total_amount" => $total_amount
            ],
            "sold_items_recorded" => $itemsInserted,
            "products_sold_updated" => $productsUpdated,
            "notifications" => [
                "buyer" => [
                    "saved" => $buyer_result['saved'],
                    "sent" => $buyer_result['sent']
                ],
                "seller" => [
                    "saved" => $seller_result['saved'],
                    "sent" => $seller_result['sent']
                ]
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>