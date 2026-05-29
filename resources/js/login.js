document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll('.custom-slide');
    const ovals = document.querySelectorAll('.oval');
    let currentIndex = 0;
    const totalSlides = slides.length;
    const slideInterval = 4000; // Transitions every 4 seconds

    function changeSlide() {
        // Remove active class from current slide and oval
        slides[currentIndex].classList.remove('active');
        ovals[currentIndex].classList.remove('active');

        // Increment index, looping back to 0 at the end
        currentIndex = (currentIndex + 1) % totalSlides;

        // Add active class to the new slide and oval
        slides[currentIndex].classList.add('active');
        ovals[currentIndex].classList.add('active');
    }

    // Run the interval loop automatically
    setInterval(changeSlide, slideInterval);
});