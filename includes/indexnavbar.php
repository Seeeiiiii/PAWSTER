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

    .nav-link {
        font-family : 'Convergence';
        font-size: 1.2rem;
        color : #AB8154;
    }

</style>

<header>
    <?php

    $navbar = array(
        "home" => "/PAWSTER/index.php",
        "adopt" => "/PAWSTER/adoption.php",
        "shop" => "/PAWSTER/shop.php",
        "groom" => "/PAWSTER/grooming.php",
        "signin" => "/PAWSTER/login.php",
        "userprof" => "/PAWSTER/userprof.php"
    );

    ?>

    <div class="position fixed w-100 ">
        <nav class="navbar navbar-expand-lg mx-2">
            <div class="container-fluid d-flex align-items-center ms-2">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
                    <img src="resources/images/Logo.png" alt="Logo" style="height: 4rem;" class="ms-4 me-2">
                    <span class="fw-bold mb-1 mt-2">PAWSTER</span>
                </a>

                <button class="navbar-toggler me-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-4">
                        <li class="nav-item me-3">
                            <a class="nav-link" href="<?php echo $navbar['home']; ?>">Home</a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="<?php echo $navbar['adopt']; ?>">Adopt</a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="<?php echo $navbar['shop']; ?>">Shop</a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="<?php echo $navbar['groom']; ?>">Groom</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $navbar['signin']; ?>">Sign In</a>
                        </li>
            </div>
        </nav>
    </div>
</header>