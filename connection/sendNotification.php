<?php

// /connection/notif/sendNotification.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '/var/www/html/connection/db_connection.php';
require_once '/var/www/html/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;

try {
    // ✅ 1. Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? null;
    $title = $input['title'] ?? "DaguitZone";
    $message = $input['message'] ?? null;
    
    // Validate required fields
    if (!$user_id) {
        throw new Exception("user_id is required");
    }
    
    if (!$message) {
        throw new Exception("message is required");
    }

    // ✅ 2. Save notification to database with title column
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, notif_message, created_at) 
        VALUES (?, ?, ?, NOW()) 
        RETURNING id
    ");
    $stmt->execute([$user_id, $title, $message]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $notification_id = $result['id'];

    // ✅ 3. Get user's FCM token from user_tokens table
    $stmt = $conn->prepare("SELECT fcm_token FROM user_tokens WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenRow || empty($tokenRow['fcm_token'])) {
        // User has no FCM token, but notification is saved
        echo json_encode([
            "success" => true,
            "notification_id" => $notification_id,
            "notification_saved" => true,
            "fcm_sent" => false,
            "message" => "Notification saved but user has no FCM token"
        ]);
        exit;
    }

    $fcmToken = $tokenRow['fcm_token'];

    // ✅ 4. Load Firebase credentials from ENV
    $firebaseJson = getenv('FIREBASE_CREDENTIALS');

    if (!$firebaseJson) {
        throw new Exception("FIREBASE_CREDENTIALS not found");
    }

    $credentialsArray = json_decode($firebaseJson, true);

    if (!$credentialsArray) {
        throw new Exception("Invalid Firebase JSON");
    }

    // ✅ 5. Generate OAuth2 Access Token
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
    $creds = new ServiceAccountCredentials($scopes, $credentialsArray);

    $tokenData = $creds->fetchAuthToken();

    if (!isset($tokenData['access_token'])) {
        throw new Exception("Failed to get access token");
    }

    $accessToken = $tokenData['access_token'];

    // ✅ 6. Get project ID
    $projectId = $credentialsArray['project_id'];

    // ✅ 7. SIMPLE payload that ALWAYS works
    $payload = [
        "message" => [
            "token" => $fcmToken,
            "notification" => [
                "title" => $title,
                "body" => $message
            ]
        ]
    ];

    // ✅ 8. Send request to Firebase
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        throw new Exception("Curl error: " . curl_error($ch));
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    if ($httpCode !== 200) {
        throw new Exception("Firebase API error (HTTP $httpCode): " . json_encode($responseData));
    }

    // ✅ 9. Return success response
    echo json_encode([
        "success" => true,
        "notification_id" => $notification_id,
        "notification_saved" => true,
        "fcm_sent" => true,
        "firebase_response" => $responseData
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}