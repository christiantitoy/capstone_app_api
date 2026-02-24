<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once '/var/www/html/connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
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

        // Split the search values
        $searchValues = explode(',', $options_value);
        $searchValues = array_map('trim', $searchValues); // Trim all values

        // Get all variants for this product
        $query = "SELECT * FROM product_variants
                 WHERE product_id = :product_id";

        $stmt = $conn->prepare($query);
        $stmt->execute([':product_id' => $product_id]);

        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($variants) > 0) {
            $foundVariant = null;

            foreach ($variants as $variant) {
                $dbOptions = $variant['options_json_value'] ?? '';

                // Skip if empty
                if (empty($dbOptions)) {
                    continue;
                }

                $dbValues = explode(',', $dbOptions);
                $dbValues = array_map('trim', $dbValues);

                // Check if both arrays contain the same values (order doesn't matter)
                if (count($searchValues) == count($dbValues)) {
                    $allMatch = true;
                    foreach ($searchValues as $value) {
                        if (!in_array($value, $dbValues)) {
                            $allMatch = false;
                            break;
                        }
                    }

                    if ($allMatch) {
                        $foundVariant = $variant;
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

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn = null; // Close PDO connection
?>