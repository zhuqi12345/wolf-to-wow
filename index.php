<?php
require_once __DIR__ . '/layout.php';

$testimonials = [
    [
        'name' => 'Maya R.',
        'dog' => 'Teddie',
        'image' => './content_images_optimized/Teddie.jpg',
        'quote' => 'Teddie came home calm, clean, and still full of personality. The online booking made it easy to pick a time that worked.',
    ],
    [
        'name' => 'Jordan P.',
        'dog' => 'Maisie',
        'image' => './content_images_optimized/Maisie.jpg',
        'quote' => 'Maisie is nervous about grooming, but the team kept things gentle and explained the visit clearly.',
    ],
    [
        'name' => 'Elaine C.',
        'dog' => 'Lucy',
        'image' => './content_images_optimized/Lucy.jpg',
        'quote' => 'Lucy loves daycare days. I like being able to see capacity before I request a spot.',
    ],
    [
        'name' => 'Avery T.',
        'dog' => 'Teddie',
        'image' => './content_images_optimized/Teddie.jpg',
        'quote' => 'The bath and brush estimate was close to what we paid, and pickup was right on schedule.',
    ],
    [
        'name' => 'Samira H.',
        'dog' => 'Maisie',
        'image' => './content_images_optimized/Maisie.jpg',
        'quote' => 'Staff remembered Maisie from her profile notes, which made the handoff much easier.',
    ],
    [
        'name' => 'Noah B.',
        'dog' => 'Lucy',
        'image' => './content_images_optimized/Lucy.jpg',
        'quote' => 'Clean studio, clear prices, and a very happy dog at the end of the day.',
    ],
    [
        'name' => 'Priya S.',
        'dog' => 'Teddie',
        'image' => './content_images_optimized/Teddie.jpg',
        'quote' => 'I booked grooming from my phone in a few minutes. The confirmation status in my account was useful.',
    ],
    [
        'name' => 'Chris M.',
        'dog' => 'Maisie',
        'image' => './content_images_optimized/Maisie.jpg',
        'quote' => 'The groomer handled our coat notes carefully and sent us home with practical maintenance advice.',
    ],
    [
        'name' => 'Lena W.',
        'dog' => 'Lucy',
        'image' => './content_images_optimized/Lucy.jpg',
        'quote' => 'Daycare availability is easy to understand, and the staff are steady during busy drop-off times.',
    ],
    [
        'name' => 'Marcus D.',
        'dog' => 'Teddie',
        'image' => './content_images_optimized/Teddie.jpg',
        'quote' => 'The full groom looked polished without feeling rushed. We will book the same groomer again.',
    ],
];

render_header('Edmonton Dog Grooming and Daycare', 'home');
?>

<section class="hero">
    <img src="<?= e(media_url('./content_images_optimized/About Us - TR Turner Main.jpg')) ?>" alt="A groomer greeting a freshly groomed dog" fetchpriority="high">
    <div class="hero-content">
        <span class="eyebrow">Edmonton grooming and small-group daycare</span>
        <h1>Careful dog care with clear online booking.</h1>
        <p>From Wolf to Wow helps busy Edmonton dog owners book grooming and daycare without phone tag. See live availability, save your dog details, and manage appointments from your account.</p>
        <div class="hero-actions">
            <a class="button button-coral" href="booknow.php?service=grooming">Book Grooming</a>
            <a class="button button-light" href="booknow.php?service=daycare">Book Daycare</a>
        </div>
    </div>
</section>

<section class="trust-strip" aria-label="Business highlights">
    <div><strong>5 groomers</strong><span>Every groomer can handle every listed service.</span></div>
    <div><strong>16 daycare spots</strong><span>Capacity updates as bookings are made.</span></div>
    <div><strong>14-day window</strong><span>Book up to two weeks ahead.</span></div>
    <div><strong>123 Fake Street</strong><span>Local Edmonton storefront and care team.</span></div>
</section>

<section class="band band-white">
    <div class="band-inner section-heading">
        <div>
            <span class="eyebrow">What we do</span>
            <h2>Two services, one calm handoff.</h2>
        </div>
        <p>Choose grooming when your dog needs a wash, trim, or coat care. Choose daycare when they need a supervised weekday visit with a clear drop-off window and visible spots.</p>
    </div>
    <div class="band-inner service-grid">
        <article class="service-card">
            <div class="card-collage" aria-hidden="true">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/1.jpg')) ?>" alt="" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/2.jpg')) ?>" alt="" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/3.jpg')) ?>" alt="" loading="lazy" decoding="async">
            </div>
            <div>
                <h3>Grooming</h3>
                <p>Baths, brush-outs, full grooms, nail care, teeth brushing, and seasonal flea and tick baths.</p>
                <a class="button button-outline" href="booknow.php?service=grooming">Book Grooming</a>
            </div>
        </article>
        <article class="service-card">
            <div class="card-collage" aria-hidden="true">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7367.jpg')) ?>" alt="" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7372.jpg')) ?>" alt="" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7373.jpg')) ?>" alt="" loading="lazy" decoding="async">
            </div>
            <div>
                <h3>Daycare</h3>
                <p>Morning, afternoon, and full-day care with capacity-aware booking and practical intake notes.</p>
                <a class="button button-outline" href="booknow.php?service=daycare">Book Daycare</a>
            </div>
        </article>
        <article class="service-card">
            <div class="card-collage" aria-hidden="true">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7410.JPG')) ?>" alt="" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7411.JPG')) ?>" alt="" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7419.JPG')) ?>" alt="" loading="lazy" decoding="async">
            </div>
            <div>
                <h3>Community</h3>
                <p>Photos, seasonal events, and a storefront page for product announcements and advertising.</p>
                <a class="button button-outline" href="events.php">See Events</a>
            </div>
        </article>
    </div>
</section>

<section class="band band-warm testimonial-band" aria-label="Customer feedback">
    <div class="band-inner section-heading">
        <div>
            <span class="eyebrow">Customer notes</span>
            <h2>Fresh cuts, happy pickups.</h2>
        </div>
        <p>Short notes from Edmonton dog owners who use From Wolf to Wow for grooming, daycare, and simple online booking.</p>
    </div>
    <div class="testimonial-rail" aria-label="Scrolling customer reviews">
        <div class="testimonial-track">
            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <article class="testimonial-card">
                        <img src="<?= e(media_url($testimonial['image'])) ?>" alt="<?= e($testimonial['dog']) ?> after a From Wolf to Wow visit" loading="lazy" decoding="async">
                        <div>
                            <p><?= e($testimonial['quote']) ?></p>
                            <strong><?= e($testimonial['name']) ?></strong>
                            <span><?= e($testimonial['dog']) ?>'s owner</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="band band-dark">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">What happens next</span>
            <h2>Simple booking, clear follow-up.</h2>
        </div>
        <div class="proof-grid">
            <div class="proof"><strong>1</strong><p>Choose grooming or daycare.</p></div>
            <div class="proof"><strong>2</strong><p>Pick an available date, time, groomer, or daycare period.</p></div>
            <div class="proof"><strong>3</strong><p>Confirm the request from an account and manage it later.</p></div>
        </div>
    </div>
</section>

<section class="band band-warm">
    <div class="band-inner split split-reverse">
        <div>
            <span class="eyebrow">Local trust</span>
            <h2>Everything customers need before they call.</h2>
            <p>Check the address, lobby hours, service details, staff availability, and response expectations before you book or visit.</p>
            <div class="button-row">
                <a class="button" href="contact.php">Contact Us</a>
                <a class="button button-outline" href="gallery.php">View Photos</a>
            </div>
        </div>
        <div class="media-mosaic" aria-label="From Wolf to Wow dog care photos">
            <img src="<?= e(media_url('./content_images_optimized/About us - TR Turner collage5.jpg')) ?>" alt="Staff member with a dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/Loulou groom_.jpg')) ?>" alt="Freshly groomed dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/IMG_1195.jpg')) ?>" alt="Dog daycare guest" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/Grooming/PhotoCollage_20200619_213420230.jpg')) ?>" alt="Grooming result collage" loading="lazy" decoding="async">
        </div>
    </div>
</section>

<?php render_footer(); ?>
