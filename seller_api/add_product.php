<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
require_once 'db_connection.php';

$response = array("status" => "error", "message" => "Unknown error");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If form data is sent instead of JSON
    if (empty($input)) {
        $input = $_POST;
    }
    
    $seller_id = $input['seller_id'] ?? '';
    $product_name = $input['product_name'] ?? '';
    $description = $input['description'] ?? '';
    $category = $input['category'] ?? '';
    $price = $input['price'] ?? '';
    $stock = $input['stock'] ?? '';
    $main_image_url = $input['main_image_url'] ?? '';
    $image_url = $input['image_url'] ?? '';
    
    // NEW: Variation data structure
    $variation_types = $input['variation_types'] ?? []; // Array of variation types
    $variants = $input['variants'] ?? []; // Array of variant objects
    
    if (empty($seller_id) || empty($product_name) || empty($description) || empty($category) || empty($price) || empty($stock)) {
        $response["message"] = "All fields are required";
        echo json_encode($response);
        exit;
    }

    if ($conn->connect_error) {
        $response["message"] = "Database connection failed";
        echo json_encode($response);
        exit;
    }
    
    // START TRANSACTION
    $conn->begin_transaction();
    
    try {
        // 1. Insert main product
        $stmt = $conn->prepare("INSERT INTO products (seller_id, product_name, product_description, category, price, stock, main_image_url, image_urls) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdiss", $seller_id, $product_name, $description, $category, $price, $stock, $main_image_url, $image_url);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add product: " . $conn->error);
        }
        
        $product_id = $stmt->insert_id;
        $stmt->close();
        
        // 2. Save variation types to product_options table (if any)
        if (!empty($variation_types) && is_array($variation_types)) {
            $option_stmt = $conn->prepare("INSERT INTO product_options (product_id, option_name, option_value) VALUES (?, ?, ?)");
            
            foreach ($variation_types as $type) {
                if (!empty($type['name']) && !empty($type['values'])) {
                    $option_name = $type['name'];
                    $option_values = is_array($type['values']) ? implode(',', $type['values']) : $type['values'];
                    
                    $option_stmt->bind_param("iss", $product_id, $option_name, $option_values);
                    
                    if (!$option_stmt->execute()) {
                        throw new Exception("Failed to save variation type: " . $conn->error);
                    }
                }
            }
            $option_stmt->close();
        }
        
        // 3. Save variants to product_variants table (if any)
        if (!empty($variants) && is_array($variants)) {
            // UPDATED: Added options_json_value column
            $variant_stmt = $conn->prepare("INSERT INTO product_variants (product_id, options_json, options_json_value, price, stock, sku, image_urls) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($variants as $variant) {
                $variant_price = $variant['price'] ?? $price;
                $variant_stock = $variant['stock'] ?? $stock;
                $variant_sku = $variant['sku'] ?? '';
                
                // Get variant image URLs
                $variant_image_urls = $variant['image_urls'] ?? '';
                
                // Convert options to JSON
                $options = $variant['options'] ?? [];
                $options_json = json_encode($options);
                
                // GENERATE options_json_value from options values
                $options_json_value = '';
                if (!empty($options) && is_array($options)) {
                    // Extract only the values and join with comma
                    $option_values = array_values($options);
                    
                    // Filter out empty values
                    $option_values = array_filter($option_values, function($value) {
                        return !empty($value) && trim($value) !== '';
                    });
                    
                    if (!empty($option_values)) {
                        $options_json_value = implode(',', $option_values);
                    }
                }
                
                $variant_stmt->bind_param(
                    "issdiss", 
                    $product_id, 
                    $options_json, 
                    $options_json_value, 
                    $variant_price, 
                    $variant_stock, 
                    $variant_sku, 
                    $variant_image_urls
                );
                
                if (!$variant_stmt->execute()) {
                    throw new Exception("Failed to add variant: " . $conn->error);
                }
            }
            
            $variant_stmt->close();
        }
        
        // 4. Update the has_variations flag in the products table
        $update_stmt = $conn->prepare("UPDATE products SET has_variations = ? WHERE id = ?");
        $has_variations_value = !empty($variants) ? 1 : 0;
        $update_stmt->bind_param("ii", $has_variations_value, $product_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // COMMIT TRANSACTION
        $conn->commit();
        
        $response["status"] = "success";
        $response["message"] = "Product added successfully";
        $response["product_id"] = $product_id;
        $response["has_variations"] = !empty($variants);
        
    } catch (Exception $e) {
        // ROLLBACK ON ERROR
        $conn->rollback();
        $response["message"] = $e->getMessage();
    }
    
    $conn->close();
    
} else {
    $response["message"] = "Invalid request method";
}

echo json_encode($response);
?>