<?php
require_once __DIR__ . '/layout.php';
render_header('About Us', 'about');
?>

<section class="band band-white">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">About From Wolf to Wow</span>
            <h1>New owners, established trust, better booking.</h1>
            <p>From Wolf to Wow is a dog grooming and daycare business in Edmonton. The shop has been part of the neighbourhood for years, and the new owners are focused on making the customer experience as dependable as the in-person care.</p>
            <p>That means clear services, visible availability, practical estimates, and a simple account area where customers can keep dog details current and cancel appointments when plans change.</p>
            <div class="button-row">
                <a class="button button-coral" href="booknow.php">Book Online</a>
                <a class="button button-outline" href="contact.php">Visit Us</a>
            </div>
        </div>
        <div class="media-mosaic about-mosaic" aria-label="From Wolf to Wow staff and dogs">
            <img src="<?= e(media_url('./content_images_optimized/About Us - TR Turner Main.jpg')) ?>" alt="From Wolf to Wow groomer caring for a dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/About us - TR Turner collage5.jpg')) ?>" alt="Staff member with a dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/IMG_1197.jpg')) ?>" alt="Dog guest" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/IMG_1195.jpg')) ?>" alt="Daycare dog guest" loading="lazy" decoding="async">
        </div>
    </div>
</section>

<section class="band band-warm">
    <div class="band-inner section-heading">
        <div>
            <span class="eyebrow">How we work</span>
            <h2>Small enough to know each dog.</h2>
        </div>
        <p>Daycare stays small enough for staff supervision, and grooming appointments are scheduled by groomer and start time so every dog gets focused handling.</p>
    </div>
    <div class="band-inner service-grid">
        <article class="service-card">
            <div>
                <h3>Care Notes</h3>
                <p>Customers can save dog names, sizes, and notes so booking forms are faster and less error-prone.</p>
            </div>
        </article>
        <article class="service-card">
            <div>
                <h3>Clear Follow-Up</h3>
                <p>Bookings are recorded immediately and marked pending until staff confirm the appointment.</p>
            </div>
        </article>
        <article class="service-card">
            <div>
                <h3>Useful Estimates</h3>
                <p>Booking estimates update from dog size, selected services, and groomer so customers can check the range before requesting an appointment.</p>
            </div>
        </article>
    </div>
</section>

<section class="band band-dark">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Location</span>
            <h2>Find us in Edmonton.</h2>
        </div>
        <div>
            <p><strong><?= e(BUSINESS_ADDRESS) ?></strong></p>
            <p>Call <a href="tel:<?= e(BUSINESS_PHONE) ?>">667.897.9999</a> or email <a href="mailto:<?= e(BUSINESS_EMAIL) ?>"><?= e(BUSINESS_EMAIL) ?></a>. Social links are available, but official booking changes should go through the website, phone, or email so staff can verify the account.</p>
        </div>
    </div>
</section>

<?php render_footer(); ?>
