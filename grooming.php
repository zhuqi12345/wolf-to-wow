<?php
require_once __DIR__ . '/layout.php';
$services = grooming_services();
$sizes = size_labels();
$groomerProfiles = [
    [
        'name' => 'Sarah Mitchell',
        'image' => 'images/groomers/sarah-mitchell.png',
        'focus' => 'Calm handling and coat maintenance',
        'summary' => 'Sarah is a steady, detail-focused groomer who works well with dogs that need a quieter pace and a predictable routine.',
        'best' => 'Best fit for first visits, small dogs, and regular bath-and-brush care.',
    ],
    [
        'name' => 'Jake Thornton',
        'image' => 'images/groomers/jake-thornton.png',
        'focus' => 'Everyday grooms and tidy coat resets',
        'summary' => 'Jake keeps appointments efficient without rushing the dog, with a practical style suited to active family pets.',
        'best' => 'Best fit for medium coats, maintenance trims, and full groom appointments.',
    ],
    [
        'name' => 'Priya Patel',
        'image' => 'images/groomers/priya-patel.png',
        'focus' => 'Face, feet, and finishing details',
        'summary' => 'Priya has a gentle, precise approach for dogs that need careful trimming around sensitive areas.',
        'best' => 'Best fit for detail work, tidy faces, nail care, and dogs that need extra patience.',
    ],
    [
        'name' => 'Cody Brennan',
        'image' => 'images/groomers/cody-brennan.png',
        'focus' => 'Bath visits and upbeat handling',
        'summary' => 'Cody brings an easygoing pace to bath appointments and helps energetic dogs settle into the grooming routine.',
        'best' => 'Best fit for bath visits, younger dogs, and dogs that do better with a relaxed tone.',
    ],
    [
        'name' => 'Linda Kowalski',
        'image' => 'images/groomers/linda-kowalski.png',
        'focus' => 'Senior dogs and comfort-first care',
        'summary' => 'Linda focuses on steady handling and comfort checks, especially when a dog needs a slower appointment.',
        'best' => 'Best fit for senior dogs, sensitive dogs, and longer coat maintenance sessions.',
    ],
];
render_header('Grooming', 'grooming');
?>

<section class="band band-white">
    <div class="band-inner service-layout">
        <?php service_nav('grooming'); ?>
        <div>
            <span class="eyebrow">Grooming Services</span>
            <h1>Grooming appointments you can book with confidence.</h1>
            <p class="text-measure">Choose a groomer and start time before filling out the rest of the request. The service estimate updates after size and service selection, and staff confirms the appointment before the visit.</p>
            <div class="media-mosaic" aria-label="Dog grooming results">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/1.jpg')) ?>" alt="Freshly groomed dog" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/2.jpg')) ?>" alt="Freshly groomed dog" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/3.jpg')) ?>" alt="Freshly groomed dog" loading="lazy" decoding="async">
                <img src="<?= e(media_url('./content_images_optimized/Grooming/4.jpg')) ?>" alt="Freshly groomed dog" loading="lazy" decoding="async">
            </div>
        </div>
    </div>
</section>

<section class="band band-warm">
    <div class="band-inner service-body">
        <div>
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Service menu</span>
                    <h2>Choose what your dog needs.</h2>
                </div>
                <p>Open an estimate range when you want a quick price check. Your booking estimate updates again after dog size, selected services, and groomer are chosen.</p>
            </div>
            <div class="rate-list">
                <?php foreach ($services as $key => $service): ?>
                    <article class="rate-item">
                        <div>
                            <h3><?= e($service['label']) ?></h3>
                            <p><?= e($service['summary']) ?></p>
                        </div>
                        <details class="price-details">
                            <summary>View estimate range</summary>
                            <div class="price-grid">
                                <?php foreach ($sizes as $sizeKey => $label): ?>
                                    <span><?= e($label) ?><br><strong><?= e(money((float) ($service['rates'][$sizeKey] ?? 0))) ?></strong></span>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="button-row">
                <a class="button button-coral" href="booknow.php?service=grooming">Check Grooming Availability</a>
                <a class="button button-outline" href="gallery.php#photos-grooming">See Grooming Photos</a>
            </div>
        </div>
    </div>
</section>

<section class="band band-white">
    <div class="band-inner">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Meet your groomers</span>
                <h2>Choose the handling style that fits your dog.</h2>
            </div>
            <p>Each groomer can perform every listed service, but every dog has a different pace. These profiles help customers choose a person before they use the booking grid, where each groomer is limited to one dog per time slot.</p>
        </div>
        <div class="groomer-grid">
            <?php foreach ($groomerProfiles as $profile): ?>
                <?php
                $hasProfileImage = is_file(__DIR__ . '/' . $profile['image']);
                $profileImage = $hasProfileImage ? $profile['image'] : 'images/z-image-turbo_00344_.png';
                ?>
                <article class="groomer-card">
                    <img class="<?= $hasProfileImage ? '' : 'is-fallback' ?>" src="<?= e($profileImage) ?>" alt="<?= e($profile['name']) ?> grooming a dog" loading="lazy" decoding="async">
                    <div>
                        <h3><?= e($profile['name']) ?></h3>
                        <span><?= e($profile['focus']) ?></span>
                        <p><?= e($profile['summary']) ?></p>
                        <p class="groomer-best"><?= e($profile['best']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="button-row">
            <a class="button button-coral" href="booknow.php?service=grooming">Check Groomer Availability</a>
        </div>
    </div>
</section>

<?php render_footer(); ?>
