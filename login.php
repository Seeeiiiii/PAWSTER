<DOCTYPE html>
    <html lang="en">

    <head>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
        <link rel="stylesheet" href="resources/css/login.css">
        <script src="resources/js/login.js"></script>
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

                    <h3 class="text-start px-2 py-5">Adopt, shop & care - All in one place.</h3>

                    <div class="divider-line2 text-center position-relative">
                        <span class="px-2 text-muted medium position-relative z-1">Explore Pawster</span>
                    </div>


                </div>

                <div class="col-md-6 right-panel p-5">
                    <div class="d-flex mb-3 toggle-container">
                        <button class="btn btn-toggle active flex-grow-1">Login</button>
                        <button class="btn btn-toggle flex-grow-1">Sign up</button>
                    </div>

                    <h2 class="medium text-start">Welcome back!</h2>
                    <h3 class="small text-start mb-4">Login to your Pawster account</h3>

                    <form>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email address:</label>
                            <input type="email" class="form-control custom-input" placeholder="example@gmail.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password:</label>
                            <div class="position-relative">
                                <input type="password" class="form-control custom-input" placeholder=".........">
                                <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer">
                                    <img src="resources/images/eye icon.png" alt="Show Password" style="height: 1.5rem;">
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between small mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="keepSigned">
                                <label class="form-check-label text-muted" for="keepSigned">Keep me signed in</label>
                            </div>
                            <a href="#" class="forgot-link">Forgot your password?</a>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-login w-50 mb-4 py-2">Login</button>
                        </div>

                        <div class="divider-line text-center position-relative my-3">
                            <span class="px-2 text-muted small position-relative z-1">or continue with</span>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2">
                                    <img src="resources/images/google.png" alt="Google" style="height: 1.5rem;">
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
                                Don't have an account? <a href="signup.php" class="sign-up fw-semibold">Signup</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
    </body>