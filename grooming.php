<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/headlinks.php'); ?>
    <link rel="stylesheet" href="resources/css/grooming.css">
    <link rel="stylesheet" href="resources/css/global.css">
    <script src="resources/js/grooming.js"></script>
</head>

<body>

     <div class="curve">
        <header>
            <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/navbarlight.php'); ?>
        </header>

        <div class="title text-center">
            <img src="resources/images/appointment.png" alt="appointment" style="width: 80px; height: 60px;">
            <h2>Book an Appointment</h2>
            <h3>Schedule grooming, vet check-ups & meet-and-greet for adoptions</h3>
        </div>
    </div>

    <div class="booking-section">
        <div class="container py-4">

            <div class="row g-3 mb-3">

                <div class="col-md-4">
                    <div class="booking-card h-100">
                        <p class="card-section-title">Service type:</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="svc-btn w-100 active" data-service="Grooming" data-fee="450" onclick="selectService(this)">
                                    <img src="resources/images/groom icon.png" alt="Grooming" class="svc-img">
                                    <span class="svc-label">Grooming</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Vet check-up" data-fee="600" onclick="selectService(this)">
                                    <img src="resources/images/check-up icon.png" alt="Vet check-up" class="svc-img">
                                    <span class="svc-label">Vet check-up</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Vaccination" data-fee="350" onclick="selectService(this)">
                                    <img src="resources/images/vaccine icon.png" alt="Vaccination" class="svc-img">
                                    <span class="svc-label">Vaccination</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="svc-btn w-100" data-service="Meet & greet" data-fee="200" onclick="selectService(this)">
                                    <img src="resources/images/heart icon.png" alt="Meet & greet" class="svc-img">
                                    <span class="svc-label">Meet &amp; greet</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="booking-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-section-title mb-0" id="cal-month-label">Pick a date – May 2026</p>
                            <div class="d-flex gap-2">
                                <button class="cal-nav-btn" onclick="prevMonth()"><i class="bi bi-chevron-left"></i></button>
                                <button class="cal-nav-btn" onclick="nextMonth()"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="cal-grid" id="cal-grid"></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">

                <div class="col-md-4">
                    <div class="booking-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <p class="card-section-title mb-0">Select pet:</p>
                            <button class="add-pet-btn" onclick="openAddPet()"><i class="bi bi-plus-lg me-1"></i>Add pet</button>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3" id="pet-list">
                            <button class="pet-pill active" data-pet="Dog" onclick="selectPet(this)">
                                <span class="pet-pill-icon">🐶</span> Dog
                            </button>
                            <button class="pet-pill" data-pet="Cat" onclick="selectPet(this)">
                                <span class="pet-pill-icon">🐱</span> Cat
                            </button>
                        </div>

                        <div class="add-pet-form" id="add-pet-form">
                            <p class="add-pet-form-title mb-2">What type of pet?</p>
                            <div class="quick-pet-chips mb-2">
                                <button class="chip" onclick="fillPetInput('Dog')">🐶 Dog</button>
                                <button class="chip" onclick="fillPetInput('Cat')">🐱 Cat</button>
                                <button class="chip" onclick="fillPetInput('Bird')">🐦 Bird</button>
                                <button class="chip" onclick="fillPetInput('Rabbit')">🐰 Rabbit</button>
                                <button class="chip" onclick="fillPetInput('Fish')">🐠 Fish</button>
                                <button class="chip" onclick="fillPetInput('Hamster')">🐹 Hamster</button>
                                <button class="chip" onclick="fillPetInput('Turtle')">🐢 Turtle</button>
                                <button class="chip" onclick="fillPetInput('Other')">🐾 Other</button>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" class="pet-type-input flex-grow-1" id="pet-type-input" placeholder="Or type your pet..." maxlength="30" oninput="onPetInputChange(this)">
                                <button class="pet-add-confirm" id="pet-add-confirm" onclick="confirmAddPet()" disabled>Add</button>
                            </div>
                            <button class="pet-form-cancel mt-2" onclick="closeAddPet()">Cancel</button>
                        </div>

                        <textarea class="notes-area w-100 mt-2" placeholder="Additional notes" id="booking-notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="booking-card h-100">
                        <p class="card-section-title" id="slots-label">Available time slots</p>
                        <div class="d-flex flex-wrap gap-2" id="slots-container"></div>
                    </div>
                </div>
            </div>

            <div class="booking-card summary-card">
                <p class="summary-title text-center fw-bold mb-3">Application Summary</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="summary-table">
                            <div class="summary-row">
                                <span class="s-key">Service</span>
                                <span class="s-val" id="s-service">Grooming</span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Date &amp; time</span>
                                <span class="s-val" id="s-datetime">–</span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Pet</span>
                                <span class="s-val" id="s-pet">Dog</span>
                            </div>
                            <div class="summary-row">
                                <span class="s-key">Location</span>
                                <span class="s-val">PAWSTER</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div class="summary-table">
                            <div class="summary-row">
                                <span class="s-key">Service fee</span>
                                <span class="s-val" id="s-fee">PHP 450</span>
                            </div>
                        </div>
                        <button class="confirm-btn w-100 mt-3" onclick="confirmBooking()">Confirm booking</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/includes/footer.php'); ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="resources/js/booking.js"></script>
</body>

</html>