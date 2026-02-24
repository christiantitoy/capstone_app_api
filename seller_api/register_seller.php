<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '/var/www/html/connection/db_connection.php';

try {
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
    $stmt = $conn->prepare("SELECT id FROM seller_profiles WHERE buyer_id = :buyer_id");
    $stmt->execute([':buyer_id' => $buyer_id]);

    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Seller profile already exists"]);
        exit;
    }

    // Insert new seller profile - NOW 8 FIELDS
    $stmt = $conn->prepare("
        INSERT INTO seller_profiles
        (buyer_id, fullname, shop_name, shop_description, business_address, business_type, phone_number, shop_category)
        VALUES (:buyer_id, :fullname, :shop_name, :shop_description, :business_address, :business_type, :phone_number, :shop_category)
    ");

    $result = $stmt->execute([
        ':buyer_id' => $buyer_id,
        ':fullname' => $fullname,
        ':shop_name' => $shop_name,
        ':shop_description' => $shop_description,
        ':business_address' => $business_address,
        ':business_type' => $business_type,
        ':phone_number' => $phone_number,
        ':shop_category' => $shop_category
    ]);

    if ($result) {
        echo json_encode([
            "status" => "success",
            "message" => "Seller profile created successfully",
            "seller_id" => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to create seller profile"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}

$conn = null; // Close PDO connection
?>