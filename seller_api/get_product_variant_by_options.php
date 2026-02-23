<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get query parameters
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    $options_value = isset($_GET['options_value']) ? trim($_GET['options_value']) : '';

    if ($product_id <= 0 || empty($options_value)) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and options_value are required'
        ]);
        exit;
    }

    // Clean the options_value
    $options_value = $conn->real_escape_string($options_value);
    
    try {
        // Split the search values
        $searchValues = explode(',', $options_value);
        
        // Build a more flexible SQL query
        $query = "SELECT * FROM product_variants 
                 WHERE product_id = $product_id";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $foundVariant = null;
            
            while ($row = $result->fetch_assoc()) {
                $dbOptions = $row['options_json_value'];
                $dbValues = explode(',', $dbOptions);
                
                // Check if both arrays contain the same values (order doesn't matter)
                if (count($searchValues) == count($dbValues)) {
                    $allMatch = true;
                    foreach ($searchValues as $value) {
                        $trimmedValue = trim($value);
                        if (!in_array($trimmedValue, array_map('trim', $dbValues))) {
                            $allMatch = false;
                            break;
                        }
                    }
                    
                    if ($allMatch) {
                        $foundVariant = $row;
                        break;
                    }
                }
            }
            
            if ($foundVariant) {
                echo json_encode([
                    'success' => true,
                    'variant' => $foundVariant
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Variant not found for: ' . $options_value
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No variants found for product ID: ' . $product_id
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>