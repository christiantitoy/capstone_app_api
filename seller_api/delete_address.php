<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['address_id'], $data['buyer_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'address_id and buyer_id are required'
        ]);
        exit;
    }

    $address_id = (int)$data['address_id'];
    $buyer_id   = (int)$data['buyer_id'];

    if ($address_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid address ID'
        ]);
        exit;
    }

    /**
     * 1️⃣ Get address + check ownership
     */
    $sql = "SELECT is_default FROM buyer_addresses WHERE id = :address_id AND buyer_id = :buyer_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':address_id' => $address_id,
        ':buyer_id' => $buyer_id
    ]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Address not found'
        ]);
        exit;
    }

    $is_default = (int)$address['is_default'];

    /**
     * 2️⃣ Count buyer addresses
     */
    $sql = "SELECT COUNT(*) AS total FROM buyer_addresses WHERE buyer_id = :buyer_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':buyer_id' => $buyer_id]);
    $total = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($total <= 1) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot delete the only address'
        ]);
        exit;
    }

    /**
     * 3️⃣ Delete address
     */
    $sql = "DELETE FROM buyer_addresses WHERE id = :address_id AND buyer_id = :buyer_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':address_id' => $address_id,
        ':buyer_id' => $buyer_id
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Delete failed'
        ]);
        exit;
    }

    /**
     * 4️⃣ If deleted address was default → assign another
     */
    if ($is_default) {
        $sql = "UPDATE buyer_addresses
                SET is_default = 1
                WHERE buyer_id = :buyer_id
                ORDER BY created_at ASC
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':buyer_id' => $buyer_id]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Address deleted successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>