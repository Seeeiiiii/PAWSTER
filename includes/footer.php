<style>
    .footer-box {
        background-color: #7B4F2E;
        bottom: 0;
        height: auto;
    }

    footer span {
        font-size: 1.8rem;
        font-family: 'Caprasimo';
        color: #C4956A;
    }

    .icon-box {
        width: 100%;
    }

    .icon-box i {
        font-size: 2rem;
        color: #C4956A;
    }

    .pawster-box {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .pawster-box .logo-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
    }

    .pawster-box,
    .links-box,
    .info-box {
        border-radius: 1.2rem;
        min-height: 100px;
        width: 270px;
        max-width: 100%;
        padding: 0.5rem;
        box-sizing: border-box;
    }

    .footer-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: flex-start;
        gap: 1.5rem;
    }

    @media (max-width: 575.98px) {
        .pawster-box,
        .links-box,
        .info-box {
            width: 100%;
            max-width: 320px;
        }

        .links-box,
        .info-box {
            text-align: center !important;
        }

        footer span {
            font-size: 1.4rem;
        }

        .links-box p,
        .info-box p,
        .copyright {
            font-size: 1rem;
        }
    }

    .links-box p,
    .info-box p {
        font-family: 'Cause', cursive;
        color: #F5E6D3;
        font-size: 1.2rem;
    }

    .links-box a {
        color: inherit;
        text-decoration: none;
    }

    .links-box a:hover {
        text-decoration: underline;
    }

    .copyright {
        font-family: 'Cause', cursive;
        color: #F5E6D3;
        font-size: 1.2rem;
    }
</style>

<?php
$footer_links = array(
    "home"  => "/PAWSTER/index.php",
    "adopt" => "/PAWSTER/adoption.php",
    "shop"  => "/PAWSTER/shop.php",
    "about" => "/PAWSTER/aboutus.php"
);
?>

<div class="container-fluid footer-box p-4">
    <div class="footer-row">
        <div class="pawster-box text-center">
            <div class="logo-row">
                <img src="resources/images/Logo.png" alt="Logo" style="height: 4rem;">
                <span class="fw-bold">PAWSTER</span>
            </div>
            <div class="icon-box d-flex justify-content-evenly">
                <i class="bi bi-facebook"></i>
                <i class="bi bi-instagram"></i>
                <i class="bi bi-linkedin"></i>
                <i class="bi bi-tiktok"></i>
            </div>
        </div>
        <div class="links-box text-start mt-3">
            <p><a href="<?= $footer_links['home'] ?>">Home</a></p>
            <p><a href="<?= $footer_links['adopt'] ?>">Adopt</a></p>
            <p><a href="<?= $footer_links['shop'] ?>">Shop</a></p>
            <p><a href="<?= $footer_links['about'] ?>">About us</a></p>

        </div>
        <div class="info-box text-allign-start mt-3">
            <p><i class="bi bi-geo-alt-fill"></i> Napocor Village, Tandang Sora, Quezon City</p>
            <p><i class="bi bi-telephone-fill"></i> +63 374288382</p>
            <p><i class="bi bi-envelope-fill"></i> pawster.adopt@gmail.com</p>
        </div>
    </div>

    <p class="copyright text-center">©2026 Pawster. All rights reserved.</p>
</div>