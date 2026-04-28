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

// ✅ Notification function
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

// ✅ Get delivery completion message
function getDeliveredMessage($order_id) {
    return "Your order #$order_id has been delivered successfully! Thank you for shopping with DaguitZone. We hope you love your purchase!";
}

// ✅ Send delivery notification to buyer
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
        
        // ✅ Call sendNotification.php - it handles BOTH saving and sending
        $result = sendPushNotification($buyer_id, $title, $message);
        $saved = $result['notification_saved'] ?? $result['success'] ?? false;
        $sent = $result['success'] ?? false;
        
        error_log("Delivery notification for order $order_id - Saved: " . ($saved ? 'Yes' : 'No') . ", Sent: " . ($sent ? 'Yes' : 'No'));
        
        return ['saved' => $saved, 'sent' => $sent];
        
    } catch (Exception $e) {
        error_log("Delivery Notification error: " . $e->getMessage());
        return ['saved' => false, 'sent' => false];
    }
}

// ✅ Send notification to seller
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
        $message = "Great news! Order #$order_id has been successfully delivered to the buyer. The earnings have been added to your account.";
        
        // ✅ Call sendNotification.php - it handles BOTH saving and sending
        $result = sendPushNotification($seller_id, $title, $message);
        $saved = $result['notification_saved'] ?? $result['success'] ?? false;
        $sent = $result['success'] ?? false;
        
        error_log("Seller notification for order $order_id - Saved: " . ($saved ? 'Yes' : 'No') . ", Sent: " . ($sent ? 'Yes' : 'No'));
        
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
    $otp_code = null;  // ✅ Added OTP variable
    $proof_image_path = null;

    // Handle multipart form data (from Android)
    $delivery_id = $_POST['delivery_id'] ?? null;
    $rider_id = $_POST['rider_id'] ?? null;
    $order_id = $_POST['order_id'] ?? null;
    $otp_code = $_POST['otp_code'] ?? null;  // ✅ Get OTP from request

    if (!$delivery_id || !$rider_id || !$order_id) {
        echo json_encode([
            "success" => false,
            "message" => "Missing delivery_id, rider_id, or order_id"
        ]);
        exit;
    }

    // ✅ Validate OTP
    if (!$otp_code) {
        echo json_encode([
            "success" => false,
            "message" => "OTP code is required"
        ]);
        exit;
    }

    // ✅ Check if OTP matches the order's delivery_otp
    $otpStmt = $conn->prepare("SELECT delivery_otp FROM orders WHERE id = ?");
    $otpStmt->execute([$order_id]);
    $orderData = $otpStmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderData) {
        echo json_encode([
            "success" => false,
            "message" => "Order not found"
        ]);
        exit;
    }

    if ($orderData['delivery_otp'] !== $otp_code) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid OTP. Please check the code and try again."
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

        // ✅ Send notifications after successful commit
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