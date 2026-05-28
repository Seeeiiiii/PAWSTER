<!DOCTYPE html>
<html lang="en">


<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/sellerapplication.css">
    <link rel="stylesheet" href="resources/css/global.css">
</head>

<body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); ?>
    </header>

    <div class="container market-box p-5 text-center">
        <h1 class="market-logo"><i class="bi bi-shop"></i></h1>
        <h3 class="mt-5">Become a PAWSTER seller !</h3>
        <h6>Complete the form below to apply. Our team reviews applications within 2–3 business days.</h6>
    </div>

    <div class="container fillout p-5 rounded-5 text-start mb-3">
        <h4>Business Details :</h4>
        <div class="row details-row mt-3 mb-2">
            <div class="col-6">
                <h5>Business Name:</h5>
                <input type="text" class="form-control mb-3" placeholder="Pawster Inc.">
                <h5>Contact Number:</h5>
                <input type="text" class="form-control mb-3" placeholder="+63-967-993-4105">
            </div>
            <div class="col-6">
                <h5>DTI registration no. :</h5>
                <input type="text" class="form-control mb-3" placeholder="1234567">
                <h5>BIR registration no.:</h5>
                <input type="text" class="form-control mb-3" placeholder="1234567 / 123456789-10-11-12">
            </div>
        </div>
        <span>
            <h5>Address :</h5>
            <input type="text" class="form-control mb-3" placeholder="New York, NY 10001, USA">
        </span>

        <hr class="section-divider mt-5">

        <h4>Product Category :</h4>
        <div class="row details-row mt-3 mb-2">
            <div class="col-6">
                <h5>Primary Category :</h5>
                <select name="primary-category" id="category" class="form-select">
                    <option value="Pet Food">Pet Food</option>
                    <option value="Pet Accessories">Pet Accessories</option>
                    <option value="Pet Clothes">Pet Clothes</option>
                    <option value="Grooming Supplies">Grooming Supplies</option>
                </select>
            </div>
            <div class="col-6">
                <h5>Brand Name :</h5>
                <input type="text" class="form-control mb-3" placeholder="Pedigree">
            </div>
        </div>
        <span>
            <h5>Product Description :</h5>
            <input type="text" class="form-control mb-3" placeholder="Blank is a product specially created for....">
        </span>

        <hr class="section-divider mt-5">
        
    </div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>