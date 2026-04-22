<?php
// /admin/backend/sellers/update_seller_status.php
require_once '/var/www/html/connection/db_connection.php';
require_once '../session/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sellerId = $input['seller_id'] ?? null;
$status = $input['status'] ?? null;
$reason = $input['reason'] ?? null;

if (!$sellerId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['approved', 'rejected', 'pending'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Require reason when rejecting
if ($status === 'rejected' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit;
}

/**
 * Send rejection email to seller using Brevo API
 */
function sendRejectionEmail($sellerEmail, $sellerName, $rejectionReason) {
    $apiKey = getenv('BREVO_API_KEY');
    if (!$apiKey) {
        error_log("BREVO_API_KEY not set. Rejection email not sent.");
        return false;
    }
    
    // Prepare HTML email content - Simplified without "What You Can Do Next"
    $htmlContent = "
    <html>
    <head>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; background: #f4f4f4; padding: 20px; }
        .container { max-width: 550px; background: white; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0px 4px 20px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 25px; }
        .header h2 { color: #e74c3c; margin-bottom: 5px; }
        .header p { color: #7f8c8d; margin: 0; }
        .content { margin-bottom: 25px; }
        .reason-box { background: #fdf0f0; border-left: 4px solid #e74c3c; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .reason-box h4 { color: #e74c3c; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px; }
        .reason-box p { margin: 0; color: #2c3e50; }
        .footer { text-align: center; color: #95a5a6; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; }
    </style>
    </head>
    <body>
    <div class='container'>
        <div class='header'>
            <h2>Application Status Update</h2>
            <p>Regarding your seller application on PalitOra</p>
        </div>
        
        <div class='content'>
            <p>Dear " . htmlspecialchars($sellerName) . ",</p>
            <p>Thank you for your interest in becoming a seller on <strong>PalitOra</strong>. We have carefully reviewed your application and unfortunately, we are unable to approve your seller account at this time.</p>
            
            <div class='reason-box'>
                <h4><span style='font-size: 20px;'>⚠️</span> Rejection Reason</h4>
                <p>" . nl2br(htmlspecialchars($rejectionReason)) . "</p>
            </div>
            
            <p>If you have any questions regarding this decision, please feel free to contact our support team.</p>
        </div>
        
        <div class='footer'>
            <p>© " . date('Y') . " PalitOra. All rights reserved.</p>
        </div>
    </div>
    </body>
    </html>
    ";
    
    // Plain text version
    $textContent = "Dear {$sellerName},\n\n";
    $textContent .= "Thank you for your interest in becoming a seller on PalitOra. We have carefully reviewed your application and unfortunately, we are unable to approve your seller account at this time.\n\n";
    $textContent .= "Rejection Reason:\n{$rejectionReason}\n\n";
    $textContent .= "If you have any questions regarding this decision, please feel free to contact our support team.\n\n";
    $textContent .= "© " . date('Y') . " PalitOra. All rights reserved.";
    
    // Prepare JSON payload
    $data = [
        "sender" => [
            "name" => "PalitOra Admin",
            "email" => "christiantitoy@gmail.com"
        ],
        "to" => [
            ["email" => $sellerEmail]
        ],
        "subject" => "Update on Your PalitOra Seller Application",
        "htmlContent" => $htmlContent,
        "textContent" => $textContent
    ];
    
    // Send email using cURL
    $ch = curl_init("https://api.brevo.com/v3/smtp/email");
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "content-type: application/json",
        "api-key: $apiKey"
    ]);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        error_log("Rejection email send failed for {$sellerEmail}: $error");
        return false;
    }
    
    $json = json_decode($response, true);
    
    if (isset($json['messageId'])) {
        error_log("Rejection email sent successfully to {$sellerEmail}. Message ID: {$json['messageId']}");
        return true;
    } else {
        error_log("Brevo API error for rejection email: " . $response);
        return false;
    }
}

try {
    $conn->beginTransaction();
    
    // Get seller email and name before updating
    $stmt = $conn->prepare("SELECT full_name, email FROM sellers WHERE id = ?");
    $stmt->execute([$sellerId]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        throw new Exception('Seller not found');
    }
    
    if ($status === 'rejected') {
        // Update with rejection reason
        $stmt = $conn->prepare("
            UPDATE sellers 
            SET approval_status = ?, 
                rejection_reason = ?,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $reason, $sellerId]);
        
        $conn->commit();
        
        // Send rejection email (after commit so DB is updated)
        $emailSent = sendRejectionEmail($seller['email'], $seller['full_name'], $reason);
        
        echo json_encode([
            'success' => true,
            'message' => "Seller status updated to {$status}" . ($emailSent ? '. Rejection email sent.' : ' (Email notification failed)')
        ]);
    } else {
        // Update without reason (clear rejection reason if approved)
        $stmt = $conn->prepare("
            UPDATE sellers 
            SET approval_status = ?, 
                rejection_reason = NULL,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $sellerId]);
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Seller status updated to {$status}"
        ]);
    }
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>