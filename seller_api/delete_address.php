<?php
header('Content-Type: application/json');
require 'db_connection.php';

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
$sql = "SELECT is_default FROM buyer_addresses WHERE id = ? AND buyer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $address_id, $buyer_id);
$stmt->execute();
$address = $stmt->get_result()->fetch_assoc();

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
$sql = "SELECT COUNT(*) AS total FROM buyer_addresses WHERE buyer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['total'];

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
$sql = "DELETE FROM buyer_addresses WHERE id = ? AND buyer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $address_id, $buyer_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
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
            WHERE buyer_id = ?
            ORDER BY created_at ASC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
}

echo json_encode([
    'status' => 'success',
    'message' => 'Address deleted successfully'
]);
