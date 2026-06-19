<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/sellerapplication.css">
    <link rel="stylesheet" href="resources/css/global.css">
</head>

<body>
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php';
    if (session_status() === PHP_SESSION_NONE) session_start();
    $db = new DatabaseConnection();
    ?>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbar.php'); 
        include_once $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/controllers/navbar_mode_handler.php';?>
    </header>

    <div class="container market-box mt-5 p-5 text-center">
        <h1 class="market-logo"><i class="bi bi-shop"></i></h1>
        <h3 class="mt-5">Become a PAWSTER seller !</h3>
        <h6>Complete the form below to apply. Our team reviews applications within 2–3 business days.</h6>
    </div>

    <form action="/PAWSTER/authentication/app_form_auth.php" method="POST" enctype="multipart/form-data">
    <div class="container fillout p-5 rounded-5 text-start mb-3">
        <h4>Business Details :</h4>
        <div class="row details-row mt-3 mb-2">
            <div class="col-6">
                <h5>Business Name:</h5>
                <input type="text" name="business_name" class="form-control mb-3" placeholder="Pawster Inc.">
                <h5>Contact Number:</h5>
                <div class="mb-3">
                    <div class="input-group">
                        <select name="country_code" class="form-select custom-input" style="max-width: 110px;">
                            <option value="+63">+63 (PH)</option>
                        </select>
                        <input type="tel" name="contact_number" class="form-control custom-input" maxlength="10" placeholder="917-000-0000" required>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <h5>DTI registration no. :</h5>
                <input type="text" name="dti_reg" class="form-control mb-3" placeholder="1234567" maxlength="7" inputmode="numeric" data-numeric>
                <h5>BIR registration no.:</h5>
                <input type="text" name="bir_reg" class="form-control mb-3" placeholder="123456789" maxlength="9" inputmode="numeric" data-numeric>
            </div>
        </div>
        <span>
            <h5>Address :</h5>
            <input type="text" name="address" class="form-control mb-3" placeholder="New York, NY 10001, USA">
        </span>

        <hr class="section-divider mt-5">

        <h4>Upload Documents :</h4>
        <div class="d-flex gap-5 mt-3 text-center">
            <div class="p-3 business-permit flex-fill rounded-5">
                <i class="bi bi-file-earmark-plus"></i>
                <div class="h5 mt-2">Upload photo of business permit</div>
                <div>This website accepts PNG only , maximum of 5mb</div>
                <input type="file" name="business_permit" accept="image/png" class="form-control mt-2">
            </div>
            <div class="p-3 gov-id flex-fill rounded-5">
                <i class="bi bi-person-vcard-fill"></i>
                <div class="h5 mt-2">Upload photo of government ID</div>
                <div>This website accepts PNG only , maximum of 5mb</div>
                <input type="file" name="valid_id" accept="image/png" class="form-control mt-2">
            </div>
        </div>

        <div class="d-flex flex-column align-items-center p-4">
            <div class="form-check d-flex align-items-center">
                <input class="form-check-input me-2" type="checkbox" name="confirm1" id="confirm1" value="1">
                <label class="form-check-label mt-4" for="confirm1">
                    I confirm that all information provided is accurate and complete, and I agree to Pawster's seller
                    terms and conditions.
                </label>
            </div>

            <div class="form-check d-flex align-items-center">
                <input class="form-check-input me-2" type="checkbox" name="confirm2" id="confirm2" value="1">
                <label class="form-check-label mt-2" for="confirm2">
                    I understand that my application may be rejected if documents are incomplete or invalid.
                </label>
            </div>
        </div>

        <div class="d-flex align-items-between mt-3">
            <div class="col">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='index.php'">
                    <i class="bi bi-arrow-left"></i>
                    Cancel Application
                </button>
            </div>
            <div class="col text-end">
                <button type="submit" name="application_btn" class="btn btn-submit">
                    Submit Application
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
    </form>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-numeric]').forEach(function (input) {
            input.addEventListener('keydown', function (e) {
                const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
                if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            input.addEventListener('paste', function (e) {
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                if (!/^\d+$/.test(pasted)) {
                    e.preventDefault();
                }
            });
        });
        
        document.querySelector('input[name="contact_number"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>