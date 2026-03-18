<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
require_once '/var/www/html/connection/db_connection.php';

$response = ["status" => "error", "message" => "Unknown error"];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // If form data is sent instead of JSON
        if (empty($input)) {
            $input = $_POST;
        }

        $seller_id = $input['seller_id'] ?? '';
        $employee_id = $input['employee_id'] ?? ''; // New employee_id field
        $product_name = $input['product_name'] ?? '';
        $description = $input['description'] ?? '';
        $category = $input['category'] ?? '';
        $price = $input['price'] ?? '';
        $stock = $input['stock'] ?? '';
        $main_image_url = $input['main_image_url'] ?? '';
        $image_url = $input['image_url'] ?? '';

        // Variation data structure
        $variation_types = $input['variation_types'] ?? []; // Array of variation types
        $variants = $input['variants'] ?? []; // Array of variant objects

        // Validate required fields including employee_id
        if (empty($seller_id) || empty($employee_id) || empty($product_name) || empty($description) || empty($category) || empty($price) || empty($stock)) {
            $response["message"] = "All fields including employee_id are required";
            echo json_encode($response);
            exit;
        }

        // START TRANSACTION
        $conn->beginTransaction();

        // First verify that the employee exists and belongs to this seller (optional but recommended)
        $check_employee = $conn->prepare("SELECT id FROM employees WHERE id = :employee_id AND seller_id = :seller_id");
        $check_employee->execute([
            ':employee_id' => $employee_id,
            ':seller_id' => $seller_id
        ]);
        
        if ($check_employee->rowCount() === 0) {
            throw new Exception("Invalid employee for this seller");
        }

        // 1. Insert main item with employee_id
        $stmt = $conn->prepare("INSERT INTO items (seller_id, employee_id, product_name, product_description, category, price, stock, main_image_url, image_urls) VALUES (:seller_id, :employee_id, :product_name, :description, :category, :price, :stock, :main_image_url, :image_url)");

        $result = $stmt->execute([
            ':seller_id' => $seller_id,
            ':employee_id' => $employee_id, // Add employee_id parameter
            ':product_name' => $product_name,
            ':description' => $description,
            ':category' => $category,
            ':price' => $price,
            ':stock' => $stock,
            ':main_image_url' => $main_image_url,
            ':image_url' => $image_url
        ]);

        if (!$result) {
            throw new Exception("Failed to add item");
        }

        $item_id = $conn->lastInsertId();

        // 2. Save variation types to item_options table (if any) - KEEPING ORIGINAL LOGIC
        if (!empty($variation_types) && is_array($variation_types)) {
            $option_stmt = $conn->prepare("INSERT INTO item_options (item_id, option_name, option_value) VALUES (:item_id, :option_name, :option_value)");

            foreach ($variation_types as $type) {
                if (!empty($type['name']) && !empty($type['values'])) {
                    $option_name = $type['name'];
                    // Store as comma-separated string (original working logic)
                    $option_values = is_array($type['values']) ? implode(',', $type['values']) : $type['values'];

                    $result = $option_stmt->execute([
                        ':item_id' => $item_id,
                        ':option_name' => $option_name,
                        ':option_value' => $option_values
                    ]);

                    if (!$result) {
                        throw new Exception("Failed to save variation type");
                    }
                }
            }
        }

        // 3. Save variants to item_variants table (if any) - KEEPING ORIGINAL LOGIC
        if (!empty($variants) && is_array($variants)) {
            $variant_stmt = $conn->prepare("INSERT INTO item_variants (item_id, options_json, options_json_value, price, stock, sku, image_urls) VALUES (:item_id, :options_json, :options_json_value, :price, :stock, :sku, :image_urls)");

            foreach ($variants as $variant) {
                $variant_price = $variant['price'] ?? $price;
                $variant_stock = $variant['stock'] ?? $stock;
                $variant_sku = $variant['sku'] ?? '';

                // Get variant image URLs
                $variant_image_urls = $variant['image_urls'] ?? '';

                // Convert options to JSON
                $options = $variant['options'] ?? [];
                $options_json = json_encode($options);

                // Generate options_json_value from options values (comma-separated string)
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

                $result = $variant_stmt->execute([
                    ':item_id' => $item_id,
                    ':options_json' => $options_json,
                    ':options_json_value' => $options_json_value,
                    ':price' => $variant_price,
                    ':stock' => $variant_stock,
                    ':sku' => $variant_sku,
                    ':image_urls' => $variant_image_urls
                ]);

                if (!$result) {
                    throw new Exception("Failed to add variant");
                }
            }
        }

        // 4. Update the has_variations flag in the items table
        $update_stmt = $conn->prepare("UPDATE items SET has_variations = :has_variations WHERE id = :item_id");
        $has_variations_value = !empty($variants) ? 1 : 0;

        $update_stmt->execute([
            ':has_variations' => $has_variations_value,
            ':item_id' => $item_id
        ]);

        // COMMIT TRANSACTION
        $conn->commit();

        // Close connection after successful commit
        $conn = null;

        $response["status"] = "success";
        $response["message"] = "Item added successfully";
        $response["item_id"] = $item_id;
        $response["has_variations"] = !empty($variants);
        $response["employee_id"] = $employee_id;

    } catch (PDOException $e) {
        // ROLLBACK ON PDO ERROR
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        // Close connection on error
        $conn = null;
        $response["message"] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        // ROLLBACK ON GENERAL ERROR
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        // Close connection on error
        $conn = null;
        $response["message"] = $e->getMessage();
    }
    
} else {
    $response["message"] = "Invalid request method";
}

// Ensure connection is closed
if (isset($conn) && $conn !== null) {
    $conn = null;
}

echo json_encode($response);
?>