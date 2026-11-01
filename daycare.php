<?php
require_once __DIR__ . '/layout.php';
$periods = daycare_periods();
$rates = daycare_rates();
$sizes = size_labels();
render_header('Daycare', 'daycare');
?>

<section class="band band-white">
    <div class="band-inner service-layout">
        <?php service_nav('daycare'); ?>
        <div>
            <span class="eyebrow">Dog Daycare</span>
            <h1>Small-group daycare with space you can check before booking.</h1>
            <p class="text-measure">See whether morning, afternoon, or full-day care has room before you complete the request. Spot counts update from current bookings so availability is clear before you sign in.</p>
            <div class="media-mosaic" aria-label="Dog daycare photos">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7367.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7368.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7369.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/IMG_7370.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
            </div>
        </div>
    </div>
</section>

<section class="band band-warm">
    <div class="band-inner service-body">
        <div>
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Care options</span>
                    <h2>Pick the period that fits the day.</h2>
                </div>
                <p>Half days and full days share the same capacity pool. A full-day booking uses both the morning and afternoon spot, while half-day bookings leave the other period available.</p>
            </div>
            <div class="daycare-grid">
                <?php foreach ($periods as $periodKey => $period): ?>
                    <article class="period-card">
                        <h3><?= e($period['label']) ?></h3>
                        <p><?= e($period['time']) ?></p>
                        <details class="price-details">
                            <summary>View estimate range</summary>
                            <div class="price-grid">
                                <?php foreach ($sizes as $sizeKey => $label): ?>
                                    <span><?= e($label) ?><br><strong><?= e(money((float) ($rates[$periodKey][$sizeKey] ?? 0))) ?></strong></span>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="button-row">
                <a class="button button-coral" href="booknow.php?service=daycare">Check Daycare Spots</a>
                <a class="button button-outline" href="gallery.php#photos-daycare">See Daycare Photos</a>
            </div>
        </div>
    </div>
</section>

<section class="band band-white">
    <div class="band-inner split split-reverse">
        <div>
            <span class="eyebrow">What dogs get</span>
            <h2>Routine, supervision, and room to settle.</h2>
            <p>Dogs are grouped with staff supervision, structured rest, potty breaks, and handling notes. Customers can keep notes with each dog profile so the team sees important details during booking.</p>
            <p>Required health and behaviour information can be discussed before the first daycare day. From Wolf to Wow reserves the right to refuse service if a dog appears sick, injured, aggressive, or unsafe for the group.</p>
        </div>
        <div class="media-mosaic" aria-label="Daycare guests">
            <img src="<?= e(media_url('./content_images_optimized/IMG_7370.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/IMG_7371.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/Lucy.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
            <img src="<?= e(media_url('./content_images_optimized/Teddie.jpg')) ?>" alt="Daycare dog" loading="lazy" decoding="async">
        </div>
    </div>
</section>

<?php render_footer(); ?>
