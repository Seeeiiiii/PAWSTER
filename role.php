<?php
include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/role.css">
    <link rel="stylesheet" href="resources/css/global.css">
</head>

<body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
    </header>

    <div class="d-flex flex-column align-items-center w-100">
        <h2 class="header display-7 fw-bold mb-2 text-center">What would you like to do today?</h2>

        <div class="divider-line text-center position-relative my-3 w-50">
            <span class="px-2 text-muted display-7 position-relative z-1">Choose your role</span>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4 justify-content-center">

            <div class="col-md-4 col-sm-6 d-flex">
                <div class="card role-card text-center p-4 border-0 w-100 d-flex flex-column align-items-center justify-content-between shadow">
                    <div class="card-content d-flex flex-column align-items-center">
                        <div class="icon-wrapper mb-4">
                            <img src="resources/images/heart icon.png" alt="Adopt a pet" class="img-fluid" style="height: 5rem;">
                        </div>
                        <h3 class="card-title fw-semibold mb-3">Adopt a pet</h3>
                        <p class="card-text text-muted px-2">Browse available dogs, cats, and more. Find your perfect companion and give them a home.</p>
                    </div>
                    <button class="btn btn-card-action mt-4 w-75"><a href="/PAWSTER/adoption.php">Adopt pets</a></button>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 d-flex">
                <div class="card role-card text-center p-4 border-0 w-100 d-flex flex-column align-items-center justify-content-between shadow">
                    <div class="card-content d-flex flex-column align-items-center">
                        <div class="icon-wrapper mb-4">
                            <img src="resources/images/shop icon.png" alt="Shop pet necessities" class="img-fluid" style="height: 5rem;">
                        </div>
                        <h3 class="card-title fw-semibold mb-3">Shop pet necessities</h3>
                        <p class="card-text text-muted px-2">Find the best deals on pet food, toys, and supplies for your furry friends.</p>
                    </div>
                    <button class="btn btn-card-action mt-4 w-75"><a href="/PAWSTER/shop.php">Start shopping</a></button>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 d-flex">
                <div class="card role-card text-center p-4 border-0 w-100 d-flex flex-column align-items-center justify-content-between shadow">
                    <div class="card-content d-flex flex-column align-items-center">
                        <div class="icon-wrapper mb-4">
                            <img src="resources/images/groom icon.png" alt="Book grooming or vet" class="img-fluid" style="height: 5rem;">
                        </div>
                        <h3 class="card-title fw-semibold mb-3">Book grooming or vet</h3>
                        <p class="card-text text-muted px-2">Schedule grooming sessions, vet check-ups, or meet-and-greet appointments for your pets.</p>
                    </div>
                    <button class="btn btn-card-action mt-4 w-75"><a href="/PAWSTER/grooming.php">Book appointment</a></button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>