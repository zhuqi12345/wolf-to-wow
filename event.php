<?php
require_once __DIR__ . '/layout.php';
$event = get_event_by_slug($_GET['event'] ?? '');
if (!$event) {
    set_flash('error', 'Event not found.');
    redirect_to('events.php');
}
render_header($event['title'], 'events');
?>

<section class="band band-white">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Community event</span>
            <h1><?= e($event['title']) ?></h1>
            <div class="button-row">
                <a class="button button-coral" href="contact.php">Ask About This Event</a>
                <a class="button button-outline" href="events.php">All Events</a>
            </div>
        </div>
        <div>
            <?php foreach ($event['paragraphs'] as $paragraph): ?>
                <p><?= e($paragraph) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php render_footer(); ?>
