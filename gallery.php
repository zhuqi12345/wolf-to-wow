<?php
require_once __DIR__ . '/layout.php';

$daycarePhotos = [
    'IMG_7367.jpg',
    'IMG_7368.jpg',
    'IMG_7369.jpg',
    'IMG_7370.jpg',
    'IMG_7371.jpg',
    'IMG_7372.jpg',
    'IMG_7373.jpg',
    'Lucy.jpg',
];

$groomingPhotos = [
    '1.jpg',
    '2.jpg',
    '3.jpg',
    '4.jpg',
    '5.jpg',
    '6.jpg',
    '7.jpg',
    '8.jpg',
];

render_header('Photos', 'gallery');
?>

<section class="band band-white">
    <div class="band-inner section-heading">
        <div>
            <span class="eyebrow">Photos</span>
            <h1>Real dogs, real visits.</h1>
        </div>
        <p>Photos help owners understand the care environment before booking. Daycare images show the playful, small-group feel, while grooming images show the kind of finish customers can expect.</p>
    </div>
</section>

<section class="band band-warm" id="photos-daycare">
    <div class="band-inner">
        <h2>Daycare moments</h2>
        <div class="gallery-grid">
            <?php foreach ($daycarePhotos as $photo): ?>
                <figure class="gallery-item">
                    <img src="<?= e(media_url('./content_images_optimized/' . $photo)) ?>" alt="Dog daycare photo" loading="lazy" decoding="async">
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="band band-white" id="photos-grooming">
    <div class="band-inner">
        <h2>Grooming results</h2>
        <div class="gallery-grid">
            <?php foreach ($groomingPhotos as $photo): ?>
                <figure class="gallery-item">
                    <img src="<?= e(media_url('./content_images_optimized/Grooming/' . $photo)) ?>" alt="Dog grooming photo" loading="lazy" decoding="async">
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_footer(); ?>
