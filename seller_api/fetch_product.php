<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connection.php';

// ✅ Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// ✅ Get seller_id (works for both GET or POST, but Retrofit uses GET)
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;

if (!$seller_id) {
    echo json_encode([
        "status" => "error",
        "message" => "seller_id is required"
    ]);
    exit;
}

// ✅ Fetch products by seller_id
$query = "
    SELECT 
        id, 
        product_name, 
        price, 
        stock, 
        main_image_url, 
        category, 
        created_at
    FROM products 
    WHERE seller_id = ? 
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

$baseUrl = "http://10.216.143.249/capstone_app_api/"; // Change if you host elsewhere
$products = [];

while ($row = $result->fetch_assoc()) {
    // Ensure full image URL
    $imageUrl = $row['main_image_url'];
    if ($imageUrl && !str_starts_with($imageUrl, "http")) {
        $imageUrl = $baseUrl . ltrim($imageUrl, "/");
    }

    $products[] = [
        "id" => (int)$row['id'],
        "name" => $row['product_name'],
        "price" => (float)$row['price'],
        "stock" => (int)$row['stock'],
        "image_url" => $imageUrl ?: "",
        "category" => $row['category'],
        "status" => "active",
        "sales" => 0,
        "created_at" => $row['created_at']
    ];
}

$stmt->close();
$conn->close();

// ✅ Response
if (empty($products)) {
    echo json_encode([
        "status" => "success",
        "message" => "No products found",
        "products" => []
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => count($products) . " products found",
        "products" => $products
    ]);
}
?>
