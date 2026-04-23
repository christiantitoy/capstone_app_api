<?php
// /connection/notif/get_notifications.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '/var/www/html/connection/db_connection.php';

try {
    // Get user_id from request
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode([
            "success" => false,
            "message" => "user_id is required"
        ]);
        exit;
    }
    
    $user_id = intval($user_id);
    
    // Get notifications with title column
    $sql = "
        SELECT 
            id,
            user_id,
            title,
            notif_message as message,
            created_at,
            is_read
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 100
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format time and add icon type
    foreach ($notifications as &$notif) {
        $notif['time_ago'] = timeAgo($notif['created_at']);
        $notif['icon_type'] = getIconType($notif['title'] . ' ' . $notif['message']);
    }
    
    echo json_encode([
        "success" => true,
        "count" => count($notifications),
        "notifications" => $notifications
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

// Helper function to format time
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " min" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return date("M d", $time);
    }
}

// Determine icon type based on title/message content
function getIconType($text) {
    $text = strtolower($text);
    
    if (strpos($text, 'packed') !== false || strpos($text, '📦') !== false) {
        return 'order';
    } elseif (strpos($text, 'shipped') !== false || strpos($text, 'way') !== false || strpos($text, '🚚') !== false) {
        return 'shipping';
    } elseif (strpos($text, 'delivered') !== false || strpos($text, '✅') !== false) {
        return 'delivered';
    } elseif (strpos($text, 'rider') !== false || strpos($text, 'assigned') !== false || strpos($text, '🛵') !== false) {
        return 'rider';
    } elseif (strpos($text, 'complete') !== false || strpos($text, '🎉') !== false) {
        return 'complete';
    } elseif (strpos($text, 'cancelled') !== false || strpos($text, 'rejected') !== false || strpos($text, '❌') !== false) {
        return 'cancelled';
    } elseif (strpos($text, 'payment') !== false || strpos($text, 'gcash') !== false || strpos($text, 'verified') !== false) {
        return 'payment';
    } elseif (strpos($text, 'picked') !== false) {
        return 'rider';
    } else {
        return 'general';
    }
}
?>