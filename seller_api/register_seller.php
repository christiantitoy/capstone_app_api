<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connection.php';

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Get POST data - NOW 7 FIELDS + buyer_id
$buyer_id          = $_POST['buyer_id']          ?? null;
$fullname          = $_POST['fullname']          ?? null;
$shop_name         = $_POST['shop_name']         ?? null;
$business_address  = $_POST['business_address']  ?? null;
$phone_number      = $_POST['phone_number']      ?? null;
$business_type     = $_POST['business_type']     ?? null;
$shop_category     = $_POST['shop_category']     ?? null; // ADD THIS
$shop_description  = $_POST['shop_description']  ?? "";

// Validate required fields - NOW 7 FIELDS + buyer_id
if (!$buyer_id || !$fullname || !$shop_name || !$business_address || !$phone_number || !$business_type || !$shop_category) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// Check if seller profile already exists
$stmt = $conn->prepare("SELECT id FROM seller_profiles WHERE buyer_id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Seller profile already exists"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert new seller profile - NOW 8 FIELDS
$stmt = $conn->prepare("
    INSERT INTO seller_profiles 
    (buyer_id, fullname, shop_name, shop_description, business_address, business_type, phone_number, shop_category)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "isssssss", // NOW 8 parameters: 1 integer + 7 strings
    $buyer_id, $fullname, $shop_name, $shop_description, 
    $business_address, $business_type, $phone_number, $shop_category
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Seller profile created successfully",
        "seller_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create seller profile: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>