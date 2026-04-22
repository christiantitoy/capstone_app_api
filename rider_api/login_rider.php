<?php
header('Content-Type: application/json');

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['email'], $data['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $email = $data['email'];
    $password = $data['password'];

    // Fetch rider by email - ADDED rejection_reason to SELECT
    $stmt = $conn->prepare("
        SELECT id, password_hash, verification_status, status, rejection_reason 
        FROM riders 
        WHERE email = :email
    ");
    $stmt->execute([':email' => $email]);

    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        echo json_encode(["status" => "email_not_found"]);
        exit;
    }

    // Verify password
    if (password_verify($password, $rider['password_hash'])) {

        // ✅ FIXED: Only update to 'online' if NOT currently 'delivering'
        if ($rider['verification_status'] === 'complete') {
            // Don't override 'delivering' status
            if ($rider['status'] !== 'delivering') {
                $updateStmt = $conn->prepare("UPDATE riders SET status = 'online' WHERE id = :id");
                $updateStmt->execute([':id' => $rider['id']]);
            }
            // If status is 'delivering', leave it alone!
        }

        // Prepare response array
        $response = [
            "status" => "success",
            "rider_id" => (int)$rider['id'],
            "verification_status" => $rider['verification_status']
        ];

        // ✅ Optionally include current status so app knows
        $response['rider_status'] = $rider['status'];

        // Include rejection_reason if status is 'rejected'
        if ($rider['verification_status'] === 'rejected') {
            $response['rejection_reason'] = $rider['rejection_reason'] ?? 'No reason provided';
        }

        echo json_encode($response);

    } else {
        echo json_encode(["status" => "wrong_password"]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn = null; // Close PDO connection
?>