<!DOCTYPE html>
<html lang="en">
<?php
include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/fetchproducts.php');
$prodLoader = new prod_auto($db);
$products = $prodLoader->products;
?>

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/shop.css">
</head>

<body>

    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
    </header>

    <nav class="category-nav">
        <div class="category-container">
            <button class="category-btn active">All</button>
            <button class="category-btn">Food & Treats</button>
            <button class="category-btn">Collar & Leashes</button>
            <button class="category-btn">Grooming</button>
            <button class="category-btn">Bed & Crates</button>
            <button class="category-btn">Toys</button>
            <button class="category-btn">Health & Vet</button>
        </div>
    </nav>

    <div class="container">
        <main class="main-content">

            <section class="search-section">
                <h2 class="search-title">Search</h2>
                <div class="search-bar-main">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search for products...">
                </div>
            </section>

            <section class="products-section">
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="/PAWSTER/resources/images/<?= htmlspecialchars($product['productimage']) ?>"
                                alt="<?= htmlspecialchars($product['brand_name']) ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['brand_name']) ?></h3>
                            <p class="product-brand"><?= htmlspecialchars($product['primary_category']) ?></p>
                            <p class="product-brand"><?= htmlspecialchars($product['product_desc']) ?></p>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="pagination">
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">...</button>
                    <button class="page-btn">10</button>
                </div>
            </section>

        </main>
    </div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>