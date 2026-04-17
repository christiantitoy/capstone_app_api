<?php
require_once '/var/www/html/connection/db_connection.php';
require_once __DIR__ . '/../session/auth.php';

header('Content-Type: application/json');

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$seller_id) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $product_id = $input['product_id'] ?? 0;
    $main_price = $input['main_price'] ?? null;
    $main_stock = $input['main_stock'] ?? null;
    $variations = $input['variations'] ?? [];
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }
    
    // Verify product belongs to seller
    $stmt = $conn->prepare("SELECT has_variations FROM items WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $conn->beginTransaction();
    $updated_items = [];
    $errors = [];
    
    // Update main product if changes provided
    if ($main_price !== null || $main_stock !== null) {
        $updates = [];
        $params = [];
        
        if ($main_price !== null) {
            $updates[] = "price = ?";
            $params[] = $main_price;
            $updated_items[] = "Main product price";
        }
        
        if ($main_stock !== null) {
            $updates[] = "stock = ?";
            $params[] = $main_stock;
            $updated_items[] = "Main product stock";
        }
        
        if (!empty($updates)) {
            $updates[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $product_id;
            
            $sql = "UPDATE items SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt->execute($params)) {
                $errors[] = "Failed to update main product";
            }
        }
    }
    
    // Update variations if provided
    if (!empty($variations) && $product['has_variations'] == 1) {
        foreach ($variations as $variant) {
            $variant_id = $variant['id'] ?? 0;
            $variant_price = $variant['price'] ?? null;
            $variant_stock = $variant['stock'] ?? null;
            
            if (!$variant_id) continue;
            
            // Verify variant belongs to seller's product
            $stmt = $conn->prepare("
                SELECT iv.id FROM item_variants iv
                WHERE iv.id = ? AND iv.item_id = ?
            ");
            $stmt->execute([$variant_id, $product_id]);
            if (!$stmt->fetch()) {
                $errors[] = "Variant ID {$variant_id} not found";
                continue;
            }
            
            $updates = [];
            $params = [];
            
            if ($variant_price !== null) {
                $updates[] = "price = ?";
                $params[] = $variant_price;
                $updated_items[] = "Variant #{$variant_id} price";
            }
            
            if ($variant_stock !== null) {
                $updates[] = "stock = ?";
                $params[] = $variant_stock;
                $updated_items[] = "Variant #{$variant_id} stock";
            }
            
            if (!empty($updates)) {
                $params[] = $variant_id;
                $sql = "UPDATE item_variants SET " . implode(", ", $updates) . " WHERE id = ?";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt->execute($params)) {
                    $errors[] = "Failed to update variant #{$variant_id}";
                }
            }
        }
    }
    
    if (empty($errors)) {
        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => count($updated_items) > 0 ? 'Updates saved successfully' : 'No changes to save',
            'updated_items' => $updated_items
        ]);
    } else {
        $conn->rollBack();
        echo json_encode([
            'success' => false, 
            'message' => 'Some updates failed: ' . implode(', ', $errors)
        ]);
    }
    exit;
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}
?>