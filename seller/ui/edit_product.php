<?php
require_once __DIR__ . '/../backend/session/auth.php';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="/seller/image/app_icon.png">
    <link rel="stylesheet" href="../css/edit_product.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Edit Product</h1>
            <p>Update product price, stock, and variations</p>
        </div>
        <a href="/seller/ui/products.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div id="productContainer">
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
            <p>Loading product details...</p>
        </div>
    </div>
</div>

<script src="/seller/js/edit_product.js?v=<?= time() ?>"></script>

<script>
    // Initialize the page with PHP product ID
    const productIdFromPhp = <?= $product_id ?>;
    initEditProduct(productIdFromPhp);
</script>

</body>
</html>