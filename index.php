<!DOCTYPE html>
<html lang="en">


<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/sellerapplication.css">
    <link rel="stylesheet" href="resources/css/global.css">
    <link rel="stylesheet" href="resources/css/index.css">
</head>

<body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/indexnavbar.php'); ?>
    </header>

    <div class="container-fluid main-page p-0 mb-4 position-relative">
        <img class="w-100" src="resources/images/banner.png">
        <div class="h1 fw-bold position-absolute translate-middle-y p-5">Think they are cute?<br>
            Give them a chance and <br> take them home!</div>
        <div class="h4 fw-bold position-absolute translate-middle-y p-5">Adopt, Shop & Grooming</div>
    </div>

    <div class="container descript p-1 position-absolute">
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

    <div class="container-fluid second-container p-5 bg-light mb-5"></div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>