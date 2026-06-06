<!DOCTYPE html>
<html lang="en">
<?php
include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/fetchproducts.php');

// ── Resolve active category from GET param ──────────────────────
$active_category = null;
if (
    isset($_GET['category']) &&
    in_array($_GET['category'], prod_auto::CATEGORIES, true)
) {
    $active_category = $_GET['category'];
}

// ── Resolve page from GET param ─────────────────────────────────
$current_page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// ── Load products ────────────────────────────────────────────────
$prodLoader   = new prod_auto($db, $active_category, $current_page, 12);
$products     = $prodLoader->products;
$total_pages  = $prodLoader->total_pages;


// ── Helper: build URL preserving current filters ────────────────
function shop_url(array $overrides = []): string {
    $params = [];
    if (isset($_GET['category'])) $params['category'] = $_GET['category'];
    if (isset($_GET['page']))     $params['page']     = $_GET['page'];
    foreach ($overrides as $k => $v) {
        if ($v === null) unset($params[$k]);
        else $params[$k] = $v;
    }
    return 'shop.php' . (count($params) ? '?' . http_build_query($params) : '');
}
?>

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/shop.css">
</head>

<body>

<header>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
</header>

<!-- ── Category Navigation ──────────────────────────────────── -->
<nav class="category-nav">
    <div class="category-container">

        <!-- "All" button clears the category filter -->
        <a href="<?= shop_url(['category' => null, 'page' => null]) ?>"
           class="category-btn <?= $active_category === null ? 'active' : '' ?>">
            All
        </a>

        <?php foreach (prod_auto::CATEGORIES as $cat): ?>
            <a href="<?= shop_url(['category' => $cat, 'page' => null]) ?>"
               class="category-btn <?= $active_category === $cat ? 'active' : '' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>

    </div>
</nav>

<div class="container">
    <main class="main-content">

        <!-- ── Search Section ───────────────────────────────── -->
        <section class="search-section">
            <h2 class="search-title">
                <?= $active_category
                    ? 'Category: ' . htmlspecialchars($active_category)
                    : 'All Products' ?>
                <span class="result-count">(<?= $prodLoader->total_count ?> items)</span>
            </h2>
            <div class="search-bar-main">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search for products..." id="searchInput">
            </div>
        </section>

        <!-- ── Product Grid ─────────────────────────────────── -->
        <section class="products-section">

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No products found<?= $active_category ? ' in <strong>' . htmlspecialchars($active_category) . '</strong>' : '' ?>.</p>
                    <a href="shop.php" class="category-btn active" style="display:inline-block;margin-top:12px;">View All</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" data-name="<?= htmlspecialchars(strtolower($product['brand_name'])) ?>"
                                              data-desc="<?= htmlspecialchars(strtolower($product['product_desc'])) ?>">
                        <div class="product-image">
                            <img src="/PAWSTER/resources/images/<?= htmlspecialchars($product['productimage']) ?>"
                                 alt="<?= htmlspecialchars($product['brand_name']) ?>"
                                 loading="lazy">
                        </div>
                        <div class="product-info">
                            <span class="product-category-badge">
                                <?= htmlspecialchars($product['category']) ?>
                            </span>
                            <h3 class="product-title"><?= htmlspecialchars($product['brand_name']) ?></h3>
                            <p class="product-brand"><?= htmlspecialchars($product['primary_category']) ?></p>
                            <p class="product-desc"><?= htmlspecialchars($product['product_desc']) ?></p>
                            <button class="add-to-cart-btn"
                                    data-id="<?= (int) $product['productid'] ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ── Pagination ───────────────────────────────── -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">

                <!-- Prev -->
                <?php if ($current_page > 1): ?>
                <a href="<?= shop_url(['page' => $current_page - 1]) ?>" class="page-btn">‹</a>
                <?php endif; ?>

                <?php
                // Show a sliding window of page numbers
                $window = 2;
                $start  = max(1, $current_page - $window);
                $end    = min($total_pages, $current_page + $window);
                if ($start > 1): ?>
                    <a href="<?= shop_url(['page' => 1]) ?>" class="page-btn">1</a>
                    <?php if ($start > 2): ?><span class="page-btn disabled">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): ?>
                <a href="<?= shop_url(['page' => $p]) ?>"
                   class="page-btn <?= $p === $current_page ? 'active' : '' ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="page-btn disabled">…</span><?php endif; ?>
                    <a href="<?= shop_url(['page' => $total_pages]) ?>" class="page-btn"><?= $total_pages ?></a>
                <?php endif; ?>

                <!-- Next -->
                <?php if ($current_page < $total_pages): ?>
                <a href="<?= shop_url(['page' => $current_page + 1]) ?>" class="page-btn">›</a>
                <?php endif; ?>

            </div>
            <?php endif; ?>

        </section>

    </main>
</div>

<footer>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Client-side search filter (instant, no round-trip needed) ──
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.dataset.name || '';
            const desc = card.dataset.desc || '';
            card.style.display = (!q || name.includes(q) || desc.includes(q)) ? '' : 'none';
        });
    });
}

// ── Add-to-cart placeholder ─────────────────────────────────────
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        // TODO: POST to cart controller with productid = id
        console.log('Add to cart:', id);
        this.textContent = '✓ Added!';
        this.style.backgroundColor = 'var(--dark-brown)';
        setTimeout(() => {
            this.textContent = 'Add to Cart';
            this.style.backgroundColor = '';
        }, 1500);
    });
});
</script>

</body>
</html>