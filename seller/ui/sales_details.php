<?php
// /seller/ui/sales_details.php
require_once __DIR__ . '/../backend/session/auth.php';

$sale_id = $_GET['sale_id'] ?? 0;

if (!$sale_id) {
    header("Location: /seller/ui/sales.php");
    exit;
}

// Fetch sale details
require_once '/var/www/html/connection/db_connection.php';

$sale = null;
$variation_details = null;
$all_images = [];

try {
    $stmt = $conn->prepare("
        SELECT 
            si.id as sale_id,
            si.created_at as sale_date,
            oi.quantity,
            oi.unit_price,
            oi.total_price,
            oi.variation_id,
            oi.selected_options,
            i.product_name,
            i.product_description,
            i.main_image_url,
            i.image_urls,
            o.id as order_id,
            o.payment_method,
            o.subtotal,
            o.shipping_fee,
            o.platform_fee,
            o.total_amount,
            ba.recipient_name,
            ba.phone_number,
            ba.full_address
        FROM sold_items si
        JOIN order_items oi ON si.order_items_id = oi.id
        JOIN items i ON oi.product_id = i.id
        JOIN orders o ON si.orders_id = o.id
        JOIN buyer_addresses ba ON o.address_id = ba.id
        WHERE si.id = ?
    ");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sale) {
        header("Location: /seller/ui/sales.php");
        exit;
    }
    
    // Get variation details if exists
    if (!empty($sale['variation_id'])) {
        $var_stmt = $conn->prepare("
            SELECT options_json, sku, image_urls
            FROM item_variants
            WHERE id = ?
        ");
        $var_stmt->execute([$sale['variation_id']]);
        $variation_details = $var_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get all images - start with variation images if available
    if ($variation_details && !empty($variation_details['image_urls'])) {
        $var_images = explode(',', $variation_details['image_urls']);
        foreach ($var_images as $img) {
            $trimmed = trim($img);
            if (!empty($trimmed) && !in_array($trimmed, $all_images)) {
                $all_images[] = $trimmed;
            }
        }
    }
    
    // Add main product image (if not already added)
    if (!empty($sale['main_image_url'])) {
        $trimmed = trim($sale['main_image_url']);
        if (!in_array($trimmed, $all_images)) {
            $all_images[] = $trimmed;
        }
    }
    
    // Add additional product images (if not already added)
    if (!empty($sale['image_urls'])) {
        $additional = explode(',', $sale['image_urls']);
        foreach ($additional as $img) {
            $trimmed = trim($img);
            if (!empty($trimmed) && !in_array($trimmed, $all_images)) {
                $all_images[] = $trimmed;
            }
        }
    }
    
    // Parse variation options for display
    $variation_text = '';
    if ($variation_details && !empty($variation_details['options_json'])) {
        $options = json_decode($variation_details['options_json'], true);
        if (is_array($options)) {
            $variation_parts = [];
            foreach ($options as $key => $value) {
                $variation_parts[] = ucfirst($key) . ': ' . $value;
            }
            $variation_text = implode(', ', $variation_parts);
        }
    } elseif (!empty($sale['selected_options'])) {
        // Fallback to selected_options from order_items
        $variation_text = $sale['selected_options'];
    }
    
} catch (PDOException $e) {
    header("Location: /seller/ui/sales.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Details - #<?= htmlspecialchars($sale['sale_id']) ?></title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .back-btn {
            background: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            color: var(--dark);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            flex: 1;
        }

        .sale-badge {
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Main Card */
        .detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* Product Section */
        .product-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
            border-bottom: 1px solid #eef2f6;
        }

        /* Image Gallery */
        .image-gallery {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .main-image-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 12px;
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #eef2f6;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .main-image-container:hover {
            opacity: 0.9;
        }

        .main-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 4rem;
        }

        .thumbnail-strip {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
            background: #f8fafc;
        }

        .thumbnail:hover {
            border-color: var(--primary);
        }

        .thumbnail.active {
            border-color: var(--primary);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 1.5rem;
        }

        /* Product Info */
        .product-info {
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .product-variation {
            display: inline-block;
            background: #e8f4fd;
            color: var(--primary);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1rem;
            align-self: flex-start;
        }

        .product-description {
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: auto;
        }

        .info-item {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 10px;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
        }

        .info-value.price {
            color: var(--success);
        }

        /* Buyer Section */
        .buyer-section {
            padding: 2rem;
            border-bottom: 1px solid #eef2f6;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--primary);
        }

        .buyer-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .buyer-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .buyer-label {
            font-size: 0.8rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .buyer-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--dark);
        }

        /* Order Summary */
        .order-section {
            padding: 2rem;
            background: #fafbfc;
        }

        .summary-table {
            width: 100%;
            max-width: 400px;
            margin-left: auto;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 1.2rem;
            border-top: 2px solid #d1d9e0;
            border-bottom: none;
            margin-top: 0.5rem;
            padding-top: 1rem;
        }

        .summary-label {
            color: var(--gray);
        }

        .summary-value {
            font-weight: 600;
            color: var(--dark);
        }

        .summary-value.total {
            color: var(--success);
        }

        /* Fullscreen Modal */
        .fullscreen-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .fullscreen-modal.active {
            display: flex;
        }

        .fullscreen-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .close-fullscreen {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            padding: 10px;
            transition: opacity 0.2s;
            z-index: 1001;
        }

        .close-fullscreen:hover {
            opacity: 0.7;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            padding: 1rem;
            border-radius: 50%;
            transition: background 0.2s;
            z-index: 1001;
        }

        .nav-arrow:hover {
            background: rgba(255,255,255,0.2);
        }

        .nav-arrow.prev {
            left: 20px;
        }

        .nav-arrow.next {
            right: 20px;
        }

        .image-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0,0,0,0.5);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            z-index: 1001;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .product-section {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .summary-table {
                max-width: 100%;
            }

            .nav-arrow {
                font-size: 1.5rem;
                padding: 0.75rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <a href="/seller/ui/sales.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Sales
            </a>
            <h1>Sale Details</h1>
            <span class="sale-badge">
                <i class="fas fa-check-circle"></i> Completed
            </span>
        </div>

        <!-- Main Card -->
        <div class="detail-card">
            <!-- Product Section -->
            <div class="product-section">
                <!-- Image Gallery -->
                <div class="image-gallery">
                    <div class="main-image-container" id="mainImageContainer" onclick="openFullscreen()">
                        <?php if (!empty($all_images)): ?>
                            <img id="mainImage" src="<?= htmlspecialchars($all_images[0]) ?>" alt="<?= htmlspecialchars($sale['product_name']) ?>">
                        <?php else: ?>
                            <div class="main-image-placeholder">
                                <i class="fas fa-box"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($all_images) > 1): ?>
                        <div class="thumbnail-strip" id="thumbnailStrip">
                            <?php foreach ($all_images as $index => $img): ?>
                                <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="changeImage(<?= $index ?>)">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Thumbnail <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h2 class="product-name"><?= htmlspecialchars($sale['product_name']) ?></h2>
                    
                    <?php if (!empty($variation_text)): ?>
                        <div class="product-variation">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($variation_text) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($variation_details['sku'])): ?>
                        <div class="product-variation" style="background: #f0f2f5; color: var(--gray); margin-top: -0.5rem;">
                            <i class="fas fa-barcode"></i> SKU: <?= htmlspecialchars($variation_details['sku']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <p class="product-description"><?= htmlspecialchars($sale['product_description'] ?? 'No description available') ?></p>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Quantity Sold</div>
                            <div class="info-value"><?= $sale['quantity'] ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Unit Price</div>
                            <div class="info-value price">₱<?= number_format($sale['unit_price'], 2) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Price</div>
                            <div class="info-value price">₱<?= number_format($sale['total_price'], 2) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value"><?= htmlspecialchars($sale['payment_method']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Section -->
            <div class="buyer-section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i> Buyer Information
                </h3>
                <div class="buyer-details">
                    <div class="buyer-item">
                        <span class="buyer-label">Recipient Name</span>
                        <span class="buyer-value"><?= htmlspecialchars($sale['recipient_name']) ?></span>
                    </div>
                    <div class="buyer-item">
                        <span class="buyer-label">Phone Number</span>
                        <span class="buyer-value"><?= htmlspecialchars($sale['phone_number'] ?? 'N/A') ?></span>
                    </div>
                    <div class="buyer-item">
                        <span class="buyer-label">Shipping Address</span>
                        <span class="buyer-value"><?= htmlspecialchars($sale['full_address']) ?></span>
                    </div>
                    <div class="buyer-item">
                        <span class="buyer-label">Order ID</span>
                        <span class="buyer-value">#<?= $sale['order_id'] ?></span>
                    </div>
                    <div class="buyer-item">
                        <span class="buyer-label">Sale Date</span>
                        <span class="buyer-value"><?= date('F j, Y h:i A', strtotime($sale['sale_date'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-section">
                <h3 class="section-title">
                    <i class="fas fa-receipt"></i> Order Summary
                </h3>
                <div class="summary-table">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">₱<?= number_format($sale['subtotal'], 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping Fee</span>
                        <span class="summary-value">₱<?= number_format($sale['shipping_fee'], 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Platform Fee</span>
                        <span class="summary-value">₱<?= number_format($sale['platform_fee'], 2) ?></span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label">Total Amount</span>
                        <span class="summary-value total">₱<?= number_format($sale['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Modal -->
    <div id="fullscreenModal" class="fullscreen-modal" onclick="closeFullscreen()">
        <button class="close-fullscreen" onclick="closeFullscreen()">&times;</button>
        <button class="nav-arrow prev" onclick="event.stopPropagation(); navigateImage(-1)"><i class="fas fa-chevron-left"></i></button>
        <img id="fullscreenImage" src="" alt="Fullscreen view" onclick="event.stopPropagation()">
        <button class="nav-arrow next" onclick="event.stopPropagation(); navigateImage(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="image-counter" id="imageCounter">1 / <?= count($all_images) ?></div>
    </div>

    <script>
        // Store all images
        const allImages = <?= json_encode($all_images) ?>;
        let currentImageIndex = 0;

        // Change main image
        function changeImage(index) {
            currentImageIndex = index;
            const mainImage = document.getElementById('mainImage');
            if (mainImage && allImages[index]) {
                mainImage.src = allImages[index];
            }
            
            // Update thumbnail active state
            document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                if (i === index) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        // Open fullscreen
        function openFullscreen() {
            if (allImages.length === 0) return;
            
            const modal = document.getElementById('fullscreenModal');
            const fullscreenImage = document.getElementById('fullscreenImage');
            const counter = document.getElementById('imageCounter');
            
            fullscreenImage.src = allImages[currentImageIndex];
            counter.textContent = `${currentImageIndex + 1} / ${allImages.length}`;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close fullscreen
        function closeFullscreen() {
            const modal = document.getElementById('fullscreenModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Navigate images in fullscreen
        function navigateImage(direction) {
            if (allImages.length === 0) return;
            
            currentImageIndex = (currentImageIndex + direction + allImages.length) % allImages.length;
            
            const fullscreenImage = document.getElementById('fullscreenImage');
            const counter = document.getElementById('imageCounter');
            
            fullscreenImage.src = allImages[currentImageIndex];
            counter.textContent = `${currentImageIndex + 1} / ${allImages.length}`;
            
            // Also update main image and thumbnails
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                mainImage.src = allImages[currentImageIndex];
            }
            
            document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                if (i === currentImageIndex) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('fullscreenModal');
            if (!modal.classList.contains('active')) return;
            
            if (e.key === 'Escape') {
                closeFullscreen();
            } else if (e.key === 'ArrowLeft') {
                navigateImage(-1);
            } else if (e.key === 'ArrowRight') {
                navigateImage(1);
            }
        });
    </script>
</body>
</html>