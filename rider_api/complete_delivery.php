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

        // 2️⃣ Insert into delivery_proofs table (without recipient_name since it's not in the schema)
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

        // 4️⃣ Update orders table to delivered
        $sql4 = "
            UPDATE orders
            SET status = 'delivered'
            WHERE id = ?
        ";

        $stmt4 = $conn->prepare($sql4);
        $stmt4->execute([$order_id]);

        if ($stmt4->rowCount() === 0) {
            throw new Exception("Order not found");
        }

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Order marked as delivered successfully",
            "proof_image_url" => $proof_image_path
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        
        // If database fails, we might want to delete the uploaded image
        // But for now, just throw the error
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