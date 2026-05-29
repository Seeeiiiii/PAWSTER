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
    }

    .dropdown-menu {
        background-color: #FAF0E8;
    }

    .dropdown-item {
        font-family: 'Convergence', sans-serif !important;
    }
</style>

<header>
    <?php

    $navbar = array(
        "home" => "/ecommerce/index.php",
        "product" => "/ecommerce/products.php",
        "login" => "/ecommerce/login.php",
        "userprof" => "/ecommerce/userprof.php"
    );

    ?>

    <div class="position fixed w-100 ">
        <nav class="navbar navbar-expand-lg mx-2">
            <div class="container-fluid ms-2">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
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
                        <li class="nav-item">
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-circle"><span> | User <span></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="<?php echo $navbar["userprof"] ?>">User Profile</a>
                                    <a class="dropdown-item" href="#">Logout</a>
                                    <a class="dropdown-item" href="<?php echo $navbar["login"] ?>">Login</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>