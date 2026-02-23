<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'db_connection.php';

try {

	// E CHECK NATO KUNG NI CONNECT BA SA DB
	if (!$conn) {
		throw new Exception("DATABASE CONNECTION FAILED");
	}

	$sql = "SELECT id, shop_name, business_address, shop_category FROM seller_profiles";
	$result = mysqli_query($conn, $sql);

	// E CHECK NATO KUNG NI CONNECT BA SA DB
	if (!$result) {
		throw new Exception("ERROR: " .mysqli_error($conn));
	}

	$shops = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$shops[] = [
			'id' => $row['id'],
			'shop_name' => $row['shop_name'],
			'business_address' => $row['business_address'],
			'shop_category' => $row['shop_category']
		];
	}

	echo json_encode([
		'status' => 'success',
		'message' => 'YEHEY NA FETCHED NA!',
		'data' => [
			'shops' => $shops
		]
	]);

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'status' => 'error',
		'message' => $e->getMessage()
	]);
} finally {
	if ($conn) {
		mysqli_close($conn);
	}
}

?> 