<!DOCTYPE html>
<html lang="en">


<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/global.css">
    <link rel="stylesheet" href="resources/css/index.css">
</head>

<body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/indexnavbar.php'); ?>
    </header>

    <div class="container-fluid main-page p-0 position-relative">
        <img class="w-100" src="resources/images/banner.png">
        <div class="h1 fw-bold position-absolute translate-middle-y p-5">Think they are cute?<br>
            Give them a chance and <br> take them home!</div>
        <div class="h4 fw-bold position-absolute translate-middle-y p-5">Adopt, Shop & Grooming</div>
    </div>

    <div class="container descript p-1 mt-0 position-absolute">
        <div class="row">
            <div class="col">
                <div class="card shadow rounded-3 p-2 d-flex flex-row align-items-start">
                    <i class="bi bi-bag-heart ms-3"></i>
                    <div>
                        <div class="h5 ms-3 mt-2 mb-0">Shop with care</div>
                        <div class="p2 ms-3 mb-2 ">We provide a place for customers and sellers to connect</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card shadow rounded-3 p-2 d-flex flex-row align-items-start">
                    <i class="bi bi-activity ms-3"></i>
                    <div>
                        <div class="h5 ms-3 mt-2 mb-0">Vetirinary care</div>
                        <div class="p2 ms-3 mb-2 ">Your pet's health and wellness should not be compromised</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card shadow rounded-3 p-2 d-flex flex-row align-items-start">
                    <i class="bi bi-search-heart ms-3"></i>
                    <div>
                        <div class="h5 ms-3 mt-2 mb-0">Pet adoption</div>
                        <div class="p2 ms-3 mb-2 ">Let us help you find your companion, family, or even child! </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid second-container p-5">
        <div class="d-flex flex-row align-items-center">
            <div class="col dog-image p-4">
                <img class="w-100" src="resources/images/dog.jpg">
            </div>
            <div class="col dog-text p-5">
                <div class="text-group d-flex flex-column align-items-center">
                    <div class="h3 p-4 fw-bold">
                        "Because every abandoned pet deserves a visitor and every pet parent deserves the best supplies."
                    </div>
                    <div class="p4">Pawster bridges the gap between long-distance adopters and abandoned pets making it possible to meet, bond, and bring a new companion home through scheduled in-person shelter visits. Beyond adoption, we're also a trusted marketplace for pet supplies, connecting buyers with verified sellers. Whether you're adopting or shopping, we're here to help every paw find its place.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid third-container p-5 text-center">
        <div class="h1">What Do We Offer</div>
        <div class="p5">Here at Pawster, you can find a pet to love, keep them healthy with vet care, <br> and stock up on everything they need.</div>
        <div class="d-flex flex-row justify-content-around p-3 ">
            <div class="card-third shadow rounded-3 p-3 ">
                <img src="resources/images/card1.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h3 class="card-text p-3">Adoption</h3>
                </div>
            </div>
            <div class="card-third shadow rounded-3 p-3">
                <img src="resources/images/card2.jpg" class="card-img-top" >
                <div class="card-body">
                    <h3 class="card-text p-3">Vet services</h3>
                </div>
            </div>
            <div class="card-third shadow rounded-3 p-3">
                <img src="resources/images/card3.jpg" class="card-img-top">
                <div class="card-body">
                    <h3 class="card-text p-3">Pet Supplies</h3>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>