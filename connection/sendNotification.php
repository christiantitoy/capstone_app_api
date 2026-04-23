<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '/var/www/html/vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;

try {

    // ✅ 1. Load Firebase credentials from ENV
    $firebaseJson = getenv('FIREBASE_CREDENTIALS');

    if (!$firebaseJson) {
        throw new Exception("FIREBASE_CREDENTIALS not found");
    }

    $credentialsArray = json_decode($firebaseJson, true);

    if (!$credentialsArray) {
        throw new Exception("Invalid Firebase JSON");
    }

    // ✅ 2. Generate OAuth2 Access Token
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
    $creds = new ServiceAccountCredentials($scopes, $credentialsArray);

    $tokenData = $creds->fetchAuthToken();

    if (!isset($tokenData['access_token'])) {
        throw new Exception("Failed to get access token");
    }

    $accessToken = $tokenData['access_token'];

    // ✅ 3. TEMP TOKEN (replace later with DB)
    $fcmToken = "du1s3mvNR5OUIKZhcYpNEm:APA91bEoLt05J4tUJbqHT8_AHas-XpgGwat1OB7KpX089vzk8rTp7wnratEA4-6QwoypyG_ZFDgtpHEbJFzi5iviA0CWjMzqfUq1CepMjlyZRSJpp7lsJB8";

    if (!$fcmToken) {
        throw new Exception("FCM token is empty");
    }

    // ✅ 4. Get project ID
    $projectId = $credentialsArray['project_id'];

    // ✅ 5. Simplified payload that matches your Android setup
    $payload = [
        "message" => [
            "token" => $fcmToken,
            "priority" => "high",
            "notification" => [
                "title" => "Test Notification 🚀",
                "body" => "This is a test from PHP Firebase!"
            ],
            "data" => [
                "title" => "Test Notification 🚀",
                "body" => "This is a test from PHP Firebase!",
                "click_action" => "OPEN_ACTIVITY",
                "screen" => "home"
            ],
            "android" => [
                "priority" => "high",
                "notification" => [
                    "channel_id" => "high_importance_channel", // ✅ Must match Android
                    "sound" => "default",
                    "priority" => "max"
                ]
            ]
        ]
    ];

    // ✅ 6. Send request to Firebase
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);

    if ($response === false) {
        throw new Exception("Curl error: " . $curlError);
    }

    $responseData = json_decode($response, true);
    
    // Check if Firebase returned an error
    if ($httpCode !== 200) {
        throw new Exception("Firebase API error (HTTP $httpCode): " . ($responseData['error']['message'] ?? 'Unknown error'));
    }

    // ✅ 7. Return success response with details
    echo json_encode([
        "success" => true,
        "http_code" => $httpCode,
        "firebase_response" => $responseData,
        "message_name" => $responseData['name'] ?? 'N/A',
        "debug_info" => [
            "token_preview" => substr($fcmToken, 0, 20) . "...",
            "project_id" => $projectId,
            "platform_specific" => "Android, iOS, Web configured"
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString() // Remove in production
    ], JSON_PRETTY_PRINT);
    
    // Log error for debugging
    error_log("FCM Send Error: " . $e->getMessage());
}