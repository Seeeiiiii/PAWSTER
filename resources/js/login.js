document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll('.custom-slide');
    const ovals = document.querySelectorAll('.oval');
    let currentIndex = 0;
    const totalSlides = slides.length;
    const slideInterval = 4000; 

    function changeSlide() {
        slides[currentIndex].classList.remove('active');
        ovals[currentIndex].classList.remove('active');

        currentIndex = (currentIndex + 1) % totalSlides;

        slides[currentIndex].classList.add('active');
        ovals[currentIndex].classList.add('active');
    }


    setInterval(changeSlide, slideInterval);
});