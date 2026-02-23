<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$response = array("status" => "error", "message" => "No file uploaded");

if (isset($_FILES['product_image'])) {
    $uploadDir = "uploads/products/";
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = $_FILES['product_image']['name'];
    $fileTmp = $_FILES['product_image']['tmp_name'];
    $fileSize = $_FILES['product_image']['size'];
    
    // 2MB max size - better for product images
    $maxSize = 2 * 1024 * 1024; // 2MB in bytes

    if ($fileSize > $maxSize) {
        $response["message"] = "File too large. Max size is 2MB";
        echo json_encode($response);
        exit;
    }
    
    // Generate unique filename
    $newFileName = "product_" . time() . "_" . uniqid() . ".jpg";
    $uploadPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        $response["status"] = "success";
        $response["message"] = "File uploaded successfully";
        $response["image_url"] = "http://192.168.1.2/capstone_app_api/" . $uploadPath;
    } else {
        $response["message"] = "Failed to upload file";
    }
}

echo json_encode($response);
?>