<?php
require_once __DIR__ . '/layout.php';
$events = get_events();
render_header('Events', 'events');
?>

<section class="band band-white">
    <div class="band-inner section-heading">
        <div>
            <span class="eyebrow">Community events</span>
            <h1>Seasonal events for regulars and new friends.</h1>
        </div>
        <p>From photo days to park meetups, From Wolf to Wow events give customers another way to stay connected between grooming and daycare visits.</p>
    </div>
    <div class="band-inner event-grid">
        <?php foreach ($events as $event): ?>
            <article class="event-card">
                <h3><?= e($event['title']) ?></h3>
                <p><?= e($event['summary']) ?></p>
                <a class="button button-outline" href="event.php?event=<?= e($event['slug']) ?>">Read Event</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php render_footer(); ?>
