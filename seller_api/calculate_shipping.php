<?php
// /seller/backend/dashboard_backends/calculate_shipping.php

require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$buyer_id = $input['buyer_id'] ?? null;
$buyer_lat = $input['buyer_lat'] ?? null;
$buyer_lon = $input['buyer_lon'] ?? null;
$product_ids = $input['product_ids'] ?? [];

if (empty($buyer_id) || !is_numeric($buyer_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid buyer ID']);
    exit;
}

if (empty($buyer_lat) || empty($buyer_lon)) {
    echo json_encode(['status' => 'error', 'message' => 'Buyer GPS location is required']);
    exit;
}

if (empty($product_ids) || !is_array($product_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Product IDs are required']);
    exit;
}

$GEOAPIFY_API_KEY = getenv('GEOAPIFY_KEY');
$BASE_FARE = 15;
$RATE_PER_KM = 14;

try {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $sellerQuery = "
        SELECT DISTINCT 
            i.seller_id,
            s.store_name,
            s.latitude,
            s.longitude
        FROM items i
        INNER JOIN stores s ON i.seller_id = s.seller_id
        WHERE i.id IN ($placeholders)
        AND i.status = 'approved'
    ";
    
    $stmt = $conn->prepare($sellerQuery);
    $stmt->execute($product_ids);
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sellers)) {
        echo json_encode(['status' => 'error', 'message' => 'No valid products found']);
        exit;
    }
    
    $shippingBreakdown = [];
    $totalShipping = 0;
    
    foreach ($sellers as $seller) {
        $sellerLat = floatval($seller['latitude']);
        $sellerLon = floatval($seller['longitude']);
        $sellerId = $seller['seller_id'];
        $sellerName = $seller['store_name'];
        
        if ($sellerLat == 0 || $sellerLon == 0) {
            $distance = 0;
            $shippingFee = $BASE_FARE;
        } else {
            $url = "https://api.geoapify.com/v1/routing?waypoints=" . $sellerLat . "," . $sellerLon . "|" . $buyer_lat . "," . $buyer_lon . "&mode=drive&apiKey=" . $GEOAPIFY_API_KEY;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                $distanceMeters = $data['features'][0]['properties']['distance'] ?? 0;
                $distance = $distanceMeters / 1000;
            } else {
                $earthRadius = 6371;
                $lat1 = deg2rad($sellerLat);
                $lon1 = deg2rad($sellerLon);
                $lat2 = deg2rad($buyer_lat);
                $lon2 = deg2rad($buyer_lon);
                $deltaLat = $lat2 - $lat1;
                $deltaLon = $lon2 - $lon1;
                $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1) * cos($lat2) * sin($deltaLon / 2) * sin($deltaLon / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;
            }
            
            $shippingFee = $BASE_FARE + ($RATE_PER_KM * $distance);
            $shippingFee = round($shippingFee, 2);
        }
        
        $shippingBreakdown[] = [
            'seller_id' => $sellerId,
            'seller_name' => $sellerName,
            'seller_lat' => $sellerLat,
            'seller_lon' => $sellerLon,
            'distance_km' => round($distance, 2),
            'shipping_fee' => $shippingFee
        ];
        
        $totalShipping += $shippingFee;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Shipping calculated successfully',
        'total_shipping' => round($totalShipping, 2),
        'seller_count' => count($sellers),
        'sellers' => $shippingBreakdown
    ]);
    exit;
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}
?>