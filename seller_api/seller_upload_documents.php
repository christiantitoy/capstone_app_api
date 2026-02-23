<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(0);
ini_set('display_errors', 0);

require_once 'db_connection.php';

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Get POST data
$buyer_id = $_POST['buyer_id'] ?? null;
$business_id_type = $_POST['business_id_type'] ?? null;

// Validate required fields
if (!$buyer_id || !$business_id_type) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Check if seller exists
$stmt = $conn->prepare("SELECT id FROM seller_profiles WHERE buyer_id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Seller profile not found"]);
    $stmt->close();
    exit;
}

$seller = $result->fetch_assoc();
$seller_id = $seller['id'];
$stmt->close();

// Create upload folder
$uploadDir = "uploads/seller_documents/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadedFiles = [];

// Look for files with names: document_0, document_1, document_2, etc.
$fileIndex = 0;
while (isset($_FILES["document_$fileIndex"])) {
    $file = $_FILES["document_$fileIndex"];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueFileName = "seller_{$seller_id}_doc_" . time() . "_" . uniqid() . ".$fileExtension";
        $filePath = $uploadDir . $uniqueFileName;
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $fullFilePath = "http://10.216.143.249/capstone_app_api/$filePath";
            
            // Save to database
            $stmt = $conn->prepare("INSERT INTO seller_documents (seller_id, business_id_type, document_url) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $seller_id, $business_id_type, $fullFilePath);
            
            if ($stmt->execute()) {
                $uploadedFiles[] = [
                    "original_name" => $file['name'],
                    "saved_path" => $fullFilePath,
                    "document_id" => $stmt->insert_id
                ];
            }
            $stmt->close();
        }
    }
    $fileIndex++;
}

// Send response
if (empty($uploadedFiles)) {
    echo json_encode(["status" => "error", "message" => "No files were uploaded"]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => count($uploadedFiles) . " documents uploaded successfully",
        "uploaded_files" => $uploadedFiles
    ]);
}

$conn->close();
?>