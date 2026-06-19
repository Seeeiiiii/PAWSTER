<style>
    html,
    body {
        background-color: #F5E6D3;
    }

    .navbar-brand span {
        font-size: 1.7rem;
        font-family: 'Caprasimo';
        color: #F5E6D3;
    }

    .bi-person-circle {
        font-size: 1.7em;
    }

    .bi-person-circle span {
        font-size: 1.4rem;
        font-family: 'Convergence', sans-serif;
        color: #AB8154;
    }

    .dropdown button {
        background-color: #FAF0E8;
        position: relative;
        top: -0.1rem;
        border-radius: 1.25rem;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .dropdown > .btn.dropdown-toggle:hover,
    .dropdown > .btn.dropdown-toggle:focus,
    .dropdown > .btn.dropdown-toggle:active,
    .dropdown.show > .btn.dropdown-toggle {
        background-color: #F5E6D3 !important;
        color: #AB8154 !important;
    }

    .dropdown > .btn.dropdown-toggle:hover .bi-person-circle span,
    .dropdown > .btn.dropdown-toggle:focus .bi-person-circle span,
    .dropdown > .btn.dropdown-toggle:active .bi-person-circle span,
    .dropdown.show > .btn.dropdown-toggle .bi-person-circle span {
        color: #AB8154 !important;
    }

    .dropdown-menu {
        background-color: #FAF0E8;
    }

    .dropdown-item {
        font-family: 'Convergence', sans-serif !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
        background-color: #F5E6D3;
        color: #AB8154 !important;
    }

    /* ── Seller mode toggle ── */
    .mode-toggle-wrap {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin-left: 0.75rem;
    }

    .mode-label {
        font-family: 'Convergence', sans-serif;
        font-size: 0.78rem;
        color: #F5E6D3;
        white-space: nowrap;
        line-height: 1;
    }

    .mode-toggle-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        cursor: pointer;
    }

    .toggle-track {
        width: 44px;
        height: 24px;
        border-radius: 12px;
        transition: background-color 0.25s ease;
    }

    .toggle-track.seller-mode {
        fill: #FAF0E8;
    }

    .toggle-track.user-mode {
        fill: #D4C4B0;
    }

    .toggle-thumb {
        transition: transform 0.25s ease;
        fill: #AB8154;
    }

    .toggle-thumb.seller-mode {
        transform: translateX(20px);
    }

    .toggle-thumb.user-mode {
        transform: translateX(0px);
    }
</style>

<?php
$navbar = [
    "home"       => "/PAWSTER/index.php",
    "login"      => "/PAWSTER/login.php",
    "userprof"   => "/PAWSTER/userprof.php",
    "sellerform" => "/PAWSTER/sellerapplication.php",
    "sellerprof" => "/PAWSTER/sellerprofile.php",
    "shop"       => "/PAWSTER/shop.php",
    "adopt"      => "/PAWSTER/adoption.php",
    "groom"      => "/PAWSTER/grooming.php",
    "cart"       => "/PAWSTER/cart.php",
    "adminpage"  => "/PAWSTER/adminpage.php",
];

$first_name   = $_SESSION['auth_user']['first_name'] ?? 'Guest';
$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
$is_admin     = $is_logged_in && (($_SESSION['auth_user']['role'] ?? 'user') === 'admin');

$is_seller = false;
if ($is_logged_in && !$is_admin) {
    if (isset($_SESSION['is_seller'])) {
        $is_seller = (bool) $_SESSION['is_seller'];
    } else {
        $user_id = $_SESSION['auth_user']['userid'] ?? null;
        if ($user_id) {
            $stmt = $db->conn->prepare("
                SELECT 1 FROM tblsellerstatus
                WHERE userid = ? AND status = 'verified'
                LIMIT 1
            ");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            $is_seller = $stmt->num_rows > 0;
            $stmt->close();
            $_SESSION['is_seller'] = $is_seller;
        }
    }
}

$navbar_mode = ($is_seller && ($_SESSION['navbar_mode'] ?? 'user') === 'seller') ? 'seller' : 'user';
$toggle_url  = strtok($_SERVER['REQUEST_URI'], '?') . '?toggle_navbar_mode=1';
$track_class = $navbar_mode === 'seller' ? 'seller-mode' : 'user-mode';
$thumb_class = $navbar_mode === 'seller' ? 'seller-mode' : 'user-mode';
$mode_label  = $navbar_mode === 'seller' ? 'Seller Mode' : 'Buyer Mode';
?>

<header>
    <div class="w-100" style="z-index: 1000;">
        <nav class="navbar navbar-expand-lg mx-2">
            <div class="container-fluid ms-2">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="<?= $navbar['home'] ?>">
                    <img src="resources/images/Logo white.png" alt="Logo" style="height: 4rem;" class="ms-4 me-2">
                    <span class="fw-bold mb-1 mt-2">PAWSTER</span>
                </a>

                <button class="navbar-toggler me-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-5">
                        <li class="nav-item d-flex flex-row align-items-center">

                            <!-- ── Mode toggle (verified sellers only) ── -->
                            <?php if ($is_seller): ?>
                            <div class="mode-toggle-wrap me-2">
                                <span class="mode-label"><?= $mode_label ?></span>
                                <a href="<?= htmlspecialchars($toggle_url) ?>" class="mode-toggle-link"
                                   title="Switch between Buyer and Seller mode">
                                    <svg width="44" height="24" viewBox="0 0 44 24"
                                         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <rect class="toggle-track <?= $track_class ?>"
                                              x="0" y="0" width="44" height="24" rx="12"/>
                                        <circle class="toggle-thumb <?= $thumb_class ?>"
                                                cx="12" cy="12" r="9"/>
                                    </svg>
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- ── User / account dropdown ── -->
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-circle">
                                        <span> | <?= htmlspecialchars($first_name) ?></span>
                                    </i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                    <?php if ($is_admin): ?>
                                        <a class="dropdown-item" href="<?= $navbar['adminpage'] ?>">Admin Page</a>
                                        <form method="POST" action="/PAWSTER/authentication/auth_login.php" class="d-inline">
                                            <input type="hidden" name="logout_btn" value="1">
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>

                                    <?php elseif ($is_logged_in && $is_seller && $navbar_mode === 'seller'): ?>
                                        <!-- Verified seller in Seller Mode -->
                                        <a class="dropdown-item" href="<?= $navbar['sellerprof'] ?>">Seller Profile</a>
                                        <a class="dropdown-item" href="<?= $navbar['shop'] ?>">My Shop</a>
                                        <form method="POST" action="/PAWSTER/authentication/auth_login.php" class="d-inline">
                                            <input type="hidden" name="logout_btn" value="1">
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>

                                    <?php elseif ($is_logged_in): ?>
                                        <!-- Regular user OR seller in Buyer Mode -->
                                        <a class="dropdown-item" href="<?= $navbar['userprof'] ?>">User Profile</a>
                                        <a class="dropdown-item" href="<?= $navbar['adopt'] ?>">Browse Pets</a>
                                        <a class="dropdown-item" href="<?= $navbar['shop'] ?>">Browse Products</a>
                                        <a class="dropdown-item" href="<?= $navbar['groom'] ?>">Grooming Services</a>
                                        <?php if (!$is_seller): ?>
                                            <a class="dropdown-item" href="<?= $navbar['sellerform'] ?>">Become a seller!</a>
                                        <?php endif; ?>
                                        <form method="POST" action="/PAWSTER/authentication/auth_login.php" class="d-inline">
                                            <input type="hidden" name="logout_btn" value="1">
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>

                                    <?php else: ?>
                                        <a class="dropdown-item" href="<?= $navbar['login'] ?>">Login</a>
                                        <a class="dropdown-item" href="<?= $navbar['shop'] ?>">Check commerce page</a>
                                        <a class="dropdown-item" href="<?= $navbar['adopt'] ?>">Browse Pets</a>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>