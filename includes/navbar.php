<style>
    html,
    body {
        background-color: #F5E6D3;
    }

    .navbar-brand span {
        font-size: 1.7rem;
        font-family: 'Caprasimo';
        color: #AB8154;
    }

    .bi-cart-check-fill{
        font-size: 2rem;
        color: #AB8154;
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
    }

    .dropdown-menu {
        background-color: #FAF0E8;
    }

    .dropdown-item {
        font-family: 'Convergence', sans-serif !important;
    }
    
    
</style>

<?php
$navbar = array(
    "home"       => "/PAWSTER/index.php",
    "login"      => "/PAWSTER/login.php",
    "userprof"   => "/PAWSTER/userprof.php",
    "sellerform" => "/PAWSTER/sellerapplication.php",
    "sellerprof" => "/PAWSTER/sellerprofile.php",
    "shop" => "/PAWSTER/shop.php",
    "cart" => "/PAWSTER/cart.php"
);

$first_name = $_SESSION['auth_user']['first_name'] ?? 'Guest';
$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

$is_seller = false;
if ($is_logged_in) {
    $user_id = $_SESSION['auth_user']['userid'] ?? null;

    if ($user_id) {

        $stmt = $db->conn->prepare("
            SELECT 1 FROM tblsellerstatus
            WHERE userid = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->store_result();
        $is_seller = $stmt->num_rows > 0;
        $stmt->close();

       
        $_SESSION['is_seller_applicant'] = $is_seller;
    }
}
?>

<header>
        <div class=" w-100" style="z-index: 1000;">
        <nav class="navbar navbar-expand-lg mx-2">
            <div class="container-fluid ms-2">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="<?= $navbar['home'] ?>">
                    <img src="resources/images/Logo.png" alt="Logo" style="height: 4rem;" class="ms-4 me-2">
                    <span class="fw-bold mb-1 mt-2">PAWSTER</span>
                </a>

                <button class="navbar-toggler me-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-1 ">
                        <li class="nav-item d-flex flex-row align-items-center ">
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-circle">
                                        <span> | <?= htmlspecialchars($first_name) ?></span>
                                    </i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                    <?php if ($is_logged_in): ?>
                                        <a class="dropdown-item" href="<?= $navbar['userprof'] ?>">User Profile</a>
                                        <?php if ($is_seller): ?>
                                            <a class="dropdown-item" href="<?= $navbar['sellerprof'] ?>">Seller Profile</a>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?= $navbar['sellerform'] ?>">Become a seller!</a>
                                            <a class="dropdown-item" href="<?= $navbar['shop'] ?>">Check commerce page</a>
                                        <?php endif; ?>
                                        <form method="POST" action="/PAWSTER/authentication/auth_login.php" class="d-inline">
                                            <input type="hidden" name="logout_btn" value="1">
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>
                                    <?php else: ?>
                                        <a class="dropdown-item" href="<?= $navbar['login'] ?>">Login</a>
                                        <a class="dropdown-item" href="<?= $navbar['shop'] ?>">Check commerce page</a>
                                    <?php endif; ?>

                                </div>
                            </div>
                            <div>
                                <a href="<?= $navbar['cart'] ?>"><i class="bi bi-cart-check-fill ms-3"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>