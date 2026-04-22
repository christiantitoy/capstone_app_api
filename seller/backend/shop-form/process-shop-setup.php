<?php
// /seller/backend/shop-form/process-shop-setup.php
session_start();

if (!isset($_SESSION['seller_id'])) {
    header("Location: /seller/ui/login.php");
    exit;
}

$seller_id = (int)$_SESSION['seller_id'];

require_once '/var/www/html/connection/db_connection.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    error_log("Database connection not initialized in process-shop-setup.php");
    die("Database connection failed. Please contact support.");
}

try {
    $conn->beginTransaction();

    // ── Collect text fields ───────────────────────────────────────
    $store_name        = trim($_POST['store_name'] ?? '');
    $category          = trim($_POST['category'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $contact           = trim($_POST['contact'] ?? '');
    $open_time         = !empty($_POST['open_time'])  ? $_POST['open_time']        : null;
    $close_time        = !empty($_POST['close_time']) ? $_POST['close_time']       : null;
    $latitude          = !empty($_POST['latitude'])   ? (float)$_POST['latitude']  : null;
    $longitude         = !empty($_POST['longitude'])  ? (float)$_POST['longitude'] : null;
    $plus_code         = trim($_POST['plus_code'] ?? '');
    $owner_name        = trim($_POST['owner_name'] ?? '');
    $id_type           = trim($_POST['id_type'] ?? '');
    
    // GCash fields (optional)
    $gcash_name        = trim($_POST['gcash_name'] ?? '') ?: null;
    $gcash_number      = trim($_POST['gcash_number'] ?? '') ?: null;

    // Validate GCash number format if provided
    if ($gcash_number && !preg_match('/^09[0-9]{9}$/', $gcash_number)) {
        throw new Exception("Invalid GCash number format. Must be 11 digits starting with 09.");
    }

    // Get URLs from hidden fields
    $logo_url          = trim($_POST['logo_url'] ?? '')        ?: null;
    $banner_url        = trim($_POST['banner_url'] ?? '')      ?: null;
    $valid_id_json     = $_POST['valid_id_urls']    ?? '[]';
    $store_photos_json = $_POST['store_photo_urls'] ?? '[]';

    $valid_id_files    = json_decode($valid_id_json, true)    ?? [];
    $store_photo_files = json_decode($store_photos_json, true) ?? [];

    // Basic validation
    if (!$store_name || !$category || !$description || !$contact ||
        !$owner_name || !$id_type || !$plus_code ||
        $latitude === null || $longitude === null) {
        throw new Exception("Required fields are missing.");
    }

    // ── UPSERT into stores ────────────────────────────────────────
    $sql = "
        INSERT INTO public.stores (
            seller_id, store_name, category, description, contact_number,
            open_time, close_time, latitude, longitude, plus_code,
            logo_url, banner_url, owner_full_name, id_type,
            valid_id_files, store_photo_files, gcash_name, gcash_number
        ) VALUES (
            :sid, :sname, :cat, :descr, :contact,
            :otime, :ctime, :lat, :lng, :pcode,
            :logo, :banner, :owner, :idtype,
            :valid, :photos, :gcash_name, :gcash_number
        )
        ON CONFLICT (seller_id) DO UPDATE SET
            store_name        = EXCLUDED.store_name,
            category          = EXCLUDED.category,
            description       = EXCLUDED.description,
            contact_number    = EXCLUDED.contact_number,
            open_time         = EXCLUDED.open_time,
            close_time        = EXCLUDED.close_time,
            latitude          = EXCLUDED.latitude,
            longitude         = EXCLUDED.longitude,
            plus_code         = EXCLUDED.plus_code,
            logo_url          = EXCLUDED.logo_url,
            banner_url        = EXCLUDED.banner_url,
            owner_full_name   = EXCLUDED.owner_full_name,
            id_type           = EXCLUDED.id_type,
            valid_id_files    = EXCLUDED.valid_id_files,
            store_photo_files = EXCLUDED.store_photo_files,
            gcash_name        = EXCLUDED.gcash_name,
            gcash_number      = EXCLUDED.gcash_number,
            updated_at        = NOW()
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':sid'          => $seller_id,
        ':sname'        => $store_name,
        ':cat'          => $category,
        ':descr'        => $description,
        ':contact'      => $contact,
        ':otime'        => $open_time,
        ':ctime'        => $close_time,
        ':lat'          => $latitude,
        ':lng'          => $longitude,
        ':pcode'        => $plus_code,
        ':logo'         => $logo_url,
        ':banner'       => $banner_url,
        ':owner'        => $owner_name,
        ':idtype'       => $id_type,
        ':valid'        => $valid_id_files    ? '{' . implode(',', $valid_id_files) . '}'    : null,
        ':photos'       => $store_photo_files ? '{' . implode(',', $store_photo_files) . '}' : null,
        ':gcash_name'   => $gcash_name,
        ':gcash_number' => $gcash_number,
    ]);

    // ── Mark seller shop as set up (do NOT approve here) ─────────
    $conn->prepare("UPDATE public.sellers SET setup_shop = true, updated_at = NOW() WHERE id = :id")
         ->execute([':id' => $seller_id]);

    $conn->commit();

    // ── Redirect to pending approval page, NOT the dashboard ─────
    $_SESSION['approval_status'] = 'pending';
    header("Location: /seller/ui/sellerAccVerificationPage.php");
    exit;

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Shop setup error: " . $e->getMessage());
    header("Location: /seller/ui/shop-form.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>