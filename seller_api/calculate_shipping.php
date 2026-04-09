<?php
// /seller/backend/dashboard_backends/calculate_shipping.php

require_once '/var/www/html/connection/db_connection.php';

header('Content-Type: application/json');

// Get JSON input from Android app
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$buyer_id = $input['buyer_id'] ?? null;
$buyer_lat = $input['buyer_lat'] ?? null;
$buyer_lon = $input['buyer_lon'] ?? null;
$product_ids = $input['product_ids'] ?? [];

// Validation
if (empty($buyer_id) || !is_numeric($buyer_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid buyer ID'
    ]);
    exit;
}

if (empty($buyer_lat) || empty($buyer_lon)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Buyer GPS location is required'
    ]);
    exit;
}

if (empty($product_ids) || !is_array($product_ids)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product IDs are required'
    ]);
    exit;
}

// Your Geoapify API key (get free from https://www.geoapify.com)
$GEOAPIFY_API_KEY = "YOUR_GEOAPIFY_API_KEY"; // Replace with your actual key

// Shipping rate constants
$BASE_FARE = 15;   // Base fare in pesos
$RATE_PER_KM = 14; // Rate per kilometer in pesos

try {
    // Step 1: Get unique sellers from the product IDs
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
        echo json_encode([
            'status' => 'error',
            'message' => 'No valid products found'
        ]);
        exit;
    }
    
    // Step 2: Calculate distance and shipping fee for each seller using Geoapify
    $shippingBreakdown = [];
    $totalShipping = 0;
    
    foreach ($sellers as $seller) {
        $sellerLat = floatval($seller['latitude']);
        $sellerLon = floatval($seller['longitude']);
        $sellerId = $seller['seller_id'];
        $sellerName = $seller['store_name'];
        
        // Skip if seller has no coordinates
        if ($sellerLat == 0 || $sellerLon == 0) {
            $distance = 0;
            $shippingFee = $BASE_FARE;
        } else {
            // Get road distance from Geoapify API
            $distance = getGeoapifyDistance($sellerLat, $sellerLon, $buyer_lat, $buyer_lon, $GEOAPIFY_API_KEY);
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
    
    // Step 3: Return response
    echo json_encode([
        'status' => 'success',
        'message' => 'Shipping calculated successfully',
        'total_shipping' => round($totalShipping, 2),
        'seller_count' => count($sellers),
        'sellers' => $shippingBreakdown
    ]);
    exit;
    
} catch (PDOException $e) {
    error_log("Calculate shipping error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Get driving distance using Geoapify Routing API
 * @param float $lat1 Seller latitude
 * @param float $lon1 Seller longitude
 * @param float $lat2 Buyer latitude
 * @param float $lon2 Buyer longitude
 * @param string $apiKey Geoapify API key
 * @return float Distance in kilometers
 */
function getGeoapifyDistance($lat1, $lon1, $lat2, $lon2, $apiKey) {
    // Geoapify Routing API endpoint
    $url = "https://api.geoapify.com/v1/routing";
    
    $params = [
        'waypoints' => $lat1 . ',' . $lon1 . '|' . $lat2 . ',' . $lon2,
        'mode' => 'drive',
        'apiKey' => $apiKey
    ];
    
    $fullUrl = $url . '?' . http_build_query($params);
    
    // Make API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        
        // Extract distance from response (in meters)
        if (isset($data['features'][0]['properties']['distance'])) {
            $distanceMeters = $data['features'][0]['properties']['distance'];
            return $distanceMeters / 1000; // Convert to kilometers
        }
    }
    
    // Fallback: Use Haversine formula if Geoapify fails
    error_log("Geoapify API failed, using Haversine fallback for coordinates: $lat1,$lon1 to $lat2,$lon2");
    return calculateHaversineDistance($lat1, $lon1, $lat2, $lon2);
}

/**
 * Fallback: Calculate straight-line distance using Haversine formula
 * @param float $lat1 Latitude of first point
 * @param float $lon1 Longitude of first point  
 * @param float $lat2 Latitude of second point
 * @param float $lon2 Longitude of second point
 * @return float Distance in kilometers
 */
function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth radius in kilometers
    
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;
    
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1) * cos($lat2) *
         sin($deltaLon / 2) * sin($deltaLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}
?>