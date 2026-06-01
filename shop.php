<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAWSTER - Pet Shop</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="icon" href="/PAWSTER/resources/images/logo white.png">
    <link rel="icon"  href="/PAWSTER/resources/images/logo white.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="resources/css/shop.css">
</head>
<body>
    <!-- HEADER / NAVBAR -->
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
    </header>

    <!-- CATEGORY NAVIGATION -->
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

    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- MAIN CONTENT SECTION -->
        <main class="main-content">
            <!-- SEARCH SECTION -->
            <section class="search-section">
                <h2 class="search-title">Search</h2>
                <div class="search-bar-main">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search for products...">
                </div>
            </section>

            <!-- PRODUCT GRID -->
            <section class="products-section">
                <div class="product-grid">
                    <!-- Product 1 -->
                    <div class="product-card">
                        <div class="product-image">
                            <div class="badge badge-sale">SALE</div>
                            <img src="https://via.placeholder.com/250x200/c9b5a0/ffffff?text=Dog+Chew" alt="Premium Dog Chew Treats">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Premium Dog Chew Treats (500g)</h3>
                            <p class="product-brand">by PetAdvisor</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="rating-count">(248)</span>
                            </div>
                            <div class="price-section">
                                <span class="price">$12.99</span>
                                <span class="old-price">$18.99</span>
                            </div>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>

                    <!-- Product 2 -->
                    <div class="product-card">
                        <div class="product-image">
                            <img src="https://via.placeholder.com/250x200/c9b5a0/ffffff?text=Dog+Collar" alt="Adjustable Nylon Dog Collar">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Adjustable Nylon Dog Collar</h3>
                            <p class="product-brand">by PetAdvisor</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="rating-count">(156)</span>
                            </div>
                            <div class="price-section">
                                <span class="price">$8.99</span>
                            </div>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>

                    <!-- Product 3 -->
                    <div class="product-card">
                        <div class="product-image">
                            <div class="badge badge-new">NEW</div>
                            <img src="https://via.placeholder.com/250x200/c9b5a0/ffffff?text=Dog+Bed" alt="Orthopedic Dog Bed">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Orthopedic Dog Bed – Medium</h3>
                            <p class="product-brand">by PetAdvisor</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="rating-count">(89)</span>
                            </div>
                            <div class="price-section">
                                <span class="price">$45.99</span>
                            </div>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>

                    <!-- Product 4 -->
                    <div class="product-card">
                        <div class="product-image">
                            <div class="badge badge-sale">SALE</div>
                            <img src="https://via.placeholder.com/250x200/c9b5a0/ffffff?text=Grooming+Brush" alt="Cat Grooming Brush Set">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Cat Grooming Brush Set</h3>
                            <p class="product-brand">by PetAdvisor</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <i class="far fa-star"></i>
                                <span class="rating-count">(201)</span>
                            </div>
                            <div class="price-section">
                                <span class="price">$14.99</span>
                                <span class="old-price">$24.99</span>
                            </div>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>

                    <!-- Product 5 -->
                    <div class="product-card">
                        <div class="product-image">
                            <img src="https://via.placeholder.com/250x200/c9b5a0/ffffff?text=Dog+Toy" alt="Interactive Dog Toy Bundle">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Interactive Dog Toy Bundle</h3>
                            <p class="product-brand">by PetAdvisor</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="rating-count">(305)</span>
                            </div>
                            <div class="price-section">
                                <span class="price">$22.99</span>
                            </div>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>

                    <!-- Product 6 -->
                    <div class="product-card">
                        <div class="product-image">
                            <img src="https://via.placeholder.com/250x200/c9b5a0/ffffff?text=Pet+Shampoo" alt="Pet Shampoo Hypoallergenic">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Pet Shampoo – Hypoallergenic</h3>
                            <p class="product-brand">by PetAdvisor</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span class="rating-count">(152)</span>
                            </div>
                            <div class="price-section">
                                <span class="price">$9.99</span>
                            </div>
                            <button class="add-to-cart-btn">Add to cart</button>
                        </div>
                    </div>
                </div>

                <!-- PAGINATION -->
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
