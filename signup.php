<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/signup.css">
    <script src="resources/js/login.js"></script>
    <script src="resources/js/signup.js"></script>
</head>

<body>

<div class="first-container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row custom-card overflow-hidden shadow">

        <div class="col-md-6 left-panel p-3 d-flex flex-column">
            <div class="logo-area d-flex justify-content-center mb-4 align-items-center">
                <div class="logo d-flex align-items-center">
                    <img src="resources/images/logo white.png" alt="Logo" style="height: 6rem;">
                    <h2>PAWSTER</h2>
                </div>
            </div>

            <div class="second-container p-3 py-4 d-flex flex-column align-items-center position-relative" style="overflow: hidden;">
                <div class="w-100 position-relative">
                    <div class="custom-carousel">
                        <div class="custom-slide active">
                            <div class="row align-items-center">
                                <div class="col-5">
                                    <img src="resources/images/login dogcat.png" alt="Dog and Cat" style="height: 12rem; object-fit: contain;">
                                </div>
                                <div class="col-7 m-0 ps-5">
                                    <p class="medium m-0">Find your perfect furry companion and give them a loving forever home</p>
                                </div>
                            </div>
                        </div>
                        <div class="custom-slide">
                            <div class="row align-items-center">
                                <div class="col-5">
                                    <img src="resources/images/petshop.png" alt="Second Slide Pet" style="height: 12rem; object-fit: contain;">
                                </div>
                                <div class="col-7 m-0 ps-5">
                                    <p class="medium m-0">Turn your extra pet supplies into cash. List your items easily and connect with local buyers instantly.</p>
                                </div>
                            </div>
                        </div>
                        <div class="custom-slide">
                            <div class="row align-items-center">
                                <div class="col-5">
                                    <img src="resources/images/petgroom.png" alt="Third Slide Pet" style="height: 12rem; object-fit: contain;">
                                </div>
                                <div class="col-7 m-0 ps-5">
                                    <p class="medium m-0">Splish, splash, it's bath time. Set your pet up for success with our premium grooming products.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-100 d-flex justify-content-between align-items-center mt-4 px-4">
                    <div class="carousel-indicators-ovals d-flex gap-2">
                        <span class="oval active"></span>
                        <span class="oval"></span>
                        <span class="oval"></span>
                    </div>
                    <div class="paw-icon">
                        <img src="resources/images/paw icon.png" alt="Paw Icon" style="height: 2rem;">
                    </div>
                </div>
            </div>

            <h3 class="text-start px-2 py-5">Adopt, shop &amp; care - All in one place.</h3>

            <div class="divider-line2 text-center position-relative">
                <span class="px-2 text-muted medium position-relative z-1">Explore Pawster</span>
            </div>
        </div>

        <div class="col-md-6 right-panel p-5">
            <div class="d-flex mb-3 toggle-container">
                <button class="btn btn-toggle flex-grow-1" onclick="window.location='login.php'">Login</button>
                <button class="btn btn-toggle active flex-grow-1" onclick="window.location='signup.php'">Sign up</button>
            </div>

            <h4 class="medium text-start">Create Your Pawster Account!</h4>

            <?php if (!empty($_SESSION['message'])): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert" id="flash-msg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-exclamation-circle-fill flex-shrink-0" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM7.002 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0zM7.1 4.995a.905.905 0 1 0 1.8 0l-.35 3.507a.553.553 0 0 1-1.1 0z"/>
                    </svg>
                    <span><?= htmlspecialchars($_SESSION['message']) ?></span>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <form method="POST" action="authentication/auth_signup.php">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">First name:</label>
                        <input type="text" name="first_name" class="form-control custom-input" placeholder="John" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Last name:</label>
                        <input type="text" name="last_name" class="form-control custom-input" placeholder="Doe" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email address:</label>
                    <input type="email" name="email" class="form-control custom-input" placeholder="example@gmail.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contact number:</label>
                    <div class="input-group">
                        <select name="country_code" class="form-select custom-input" style="max-width: 110px;">
                            <option value="+63">+63 (PH)</option>
                            <option value="+1">+1 (US)</option>
                            <option value="+44">+44 (UK)</option>
                        </select>
                        <input type="tel" name="contact_number" class="form-control custom-input" maxlength="11" placeholder="917-000-0000" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password:</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="signup-password" class="form-control custom-input" placeholder="........." required minlength="8" oninput="checkStrength(this.value)">
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 style-toggle-eye" style="cursor: pointer;" onclick="togglePassword('signup-password', this)">
                            <img src="resources/images/eye icon.png" alt="Show Password" style="height: 1.5rem;">
                        </span>
                    </div>
                    <!-- Password strength bar -->
                    <div class="mt-2">
                        <div class="bg-light rounded" style="height:5px;">
                            <div class="password-strength-bar" id="strength-bar"></div>
                        </div>
                        <span class="strength-label mt-1 d-block" id="strength-label" style="color: #aaa;">Enter a password</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm password:</label>
                    <div class="position-relative">
                        <input type="password" name="confirm_password" id="signup-confirm" class="form-control custom-input" placeholder="........." required minlength="8" oninput="checkMatch()">
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 style-toggle-eye" style="cursor: pointer;" onclick="togglePassword('signup-confirm', this)">
                            <img src="resources/images/eye icon.png" alt="Show Password" style="height: 1.5rem;">
                        </span>
                    </div>
                    <div id="match-msg" class="mt-1" style="font-size:0.78rem; font-weight:600;"></div>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-login w-50 mb-4 py-2">Sign Up</button>
                </div>

                <div class="divider-line text-center position-relative  ">
                    <span class="px-2 text-muted small position-relative z-1 mt-">or continue with</span>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2">
                            <img src="resources/images/google.png" alt="Google" style="height: 1.5rem;" class="me-2">
                            Google
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2">
                            <img src="resources/images/facebook.png" alt="Facebook" style="height: 1.5rem;" class="me-2">
                            Facebook
                        </button>
                    </div>
                    <p class="text-center small pt-2 text-muted">
                        Have an account? <a href="login.php" class="sign-up fw-semibold">Login</a>
                    </p>
                </div>
            </form>
        </div>

    </div>
</div>


</body>
</html>