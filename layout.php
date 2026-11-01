<?php
require_once __DIR__ . '/app.php';

function nav_items(): array
{
    return [
        'home' => ['label' => 'Home', 'href' => 'index.php'],
        'about' => ['label' => 'About', 'href' => 'aboutus.php'],
        'grooming' => ['label' => 'Grooming', 'href' => 'grooming.php'],
        'daycare' => ['label' => 'Daycare', 'href' => 'daycare.php'],
        'gallery' => ['label' => 'Photos', 'href' => 'gallery.php'],
        'events' => ['label' => 'Events', 'href' => 'events.php'],
        'store' => ['label' => 'Products', 'href' => 'products.php'],
        'contact' => ['label' => 'Contact', 'href' => 'contact.php'],
    ];
}

function site_icon(string $name): string
{
    $paths = [
        'map-pin' => '<path d="M12 21s7-6.1 7-11.2A7 7 0 0 0 5 9.8C5 14.9 12 21 12 21Z"></path><circle cx="12" cy="9.8" r="2.2"></circle>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.7.6 2.5a2 2 0 0 1-.5 2.1L8 9.5a16 16 0 0 0 6.5 6.5l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.6.5 2.5.6a2 2 0 0 1 1.7 2Z"></path>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path>',
        'share' => '<circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><path d="m8.6 10.6 6.8-4.2"></path><path d="m8.6 13.4 6.8 4.2"></path>',
        'clock' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    return '<svg class="site-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths[$name] . '</svg>';
}

function render_header(string $title, string $active = ''): void
{
    $user = current_user();
    $pageTitle = $title ? $title . ' | From Wolf to Wow' : 'From Wolf to Wow';
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="From Wolf to Wow is an Edmonton dog grooming and daycare studio with online booking, careful handling, and small-group daycare.">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="images/favicon.png?v=4">
    <link rel="apple-touch-icon" href="images/z-image-turbo_00344_.png">
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
<header class="site-header">
    <div class="notice-bar">Edmonton dog grooming and daycare. Online bookings are available up to 14 days ahead.</div>
    <nav class="main-nav" aria-label="Primary navigation">
        <a class="brand" href="index.php" aria-label="From Wolf to Wow home">
            <img src="images/z-image-turbo_00344_.png" alt="From Wolf to Wow logo">
            <span>
                <strong>From Wolf to Wow</strong>
                <small>Grooming and daycare in Edmonton</small>
            </span>
        </a>
        <input class="nav-toggle-input" type="checkbox" id="site-nav-toggle" aria-label="Toggle navigation">
        <label class="nav-toggle" for="site-nav-toggle">Menu</label>
        <div class="nav-links">
            <?php foreach (nav_items() as $key => $item): ?>
                <a class="<?= $active === $key ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="account-nav">
            <?php if ($user): ?>
                <a class="button button-small button-coral" href="booknow.php">Book Now</a>
                <a class="ghost-link" href="account.php">My Account</a>
                <?php if ((int) $user['USR_IsAdmin'] === 1): ?>
                    <a class="ghost-link" href="staff.php">Staff</a>
                <?php endif; ?>
                <a class="ghost-link" href="logout.php">Log Out</a>
            <?php else: ?>
                <a class="button button-small button-coral" href="booknow.php">Book Now</a>
                <a class="ghost-link" href="login.php">Log In</a>
                <a class="ghost-link" href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
    <?php foreach (consume_flashes() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?php
}

function render_footer(): void
{
    ?>
</main>
<footer class="site-footer">
    <section class="footer-main">
        <div>
            <h2>From Wolf to Wow</h2>
            <p class="icon-line"><?= site_icon('map-pin') ?><span><?= e(BUSINESS_ADDRESS) ?></span></p>
            <p class="icon-line"><?= site_icon('phone') ?><a href="tel:<?= e(BUSINESS_PHONE) ?>">667.897.9999</a></p>
            <p class="icon-line"><?= site_icon('mail') ?><a href="mailto:<?= e(BUSINESS_EMAIL) ?>"><?= e(BUSINESS_EMAIL) ?></a></p>
        </div>
        <div>
            <h3 class="heading-icon"><?= site_icon('clock') ?><span>Lobby Hours</span></h3>
            <p>Monday to Friday, 7:30 AM - 6:00 PM</p>
            <p>Saturday by confirmed appointment</p>
            <p>Sunday and statutory holidays closed</p>
        </div>
        <div>
            <h3 class="heading-icon"><?= site_icon('share') ?><span>Social</span></h3>
            <div class="social-links">
                <?php foreach (social_links() as $label => $url): ?>
                    <a href="<?= e($url) ?>" aria-label="<?= e($label) ?>"><?= e(substr($label, 0, 1)) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="fine-print">Bookings are requests until a staff member confirms them. Estimates do not include tax or special handling fees.</p>
        </div>
    </section>
    <section class="footer-bottom">
        <span>Copyright &copy; 2026 From Wolf to Wow</span>
        <span>Small-group care, careful grooming, clear online booking.</span>
    </section>
</footer>
</body>
</html>
    <?php
}

function service_nav(string $active): void
{
    $items = [
        'grooming' => ['label' => 'Grooming', 'href' => 'grooming.php'],
        'daycare' => ['label' => 'Daycare', 'href' => 'daycare.php'],
        'booking' => ['label' => 'Availability', 'href' => 'booknow.php'],
        'gallery' => ['label' => 'Photos', 'href' => 'gallery.php'],
        'contact' => ['label' => 'Questions', 'href' => 'contact.php'],
    ];
    ?>
    <aside class="service-nav" aria-label="Service navigation">
        <span>Services</span>
        <?php foreach ($items as $key => $item): ?>
            <a class="<?= $active === $key ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
        <?php endforeach; ?>
    </aside>
    <?php
}
?>
