<?php
require_once __DIR__ . '/layout.php';
render_header('Storefront', 'store');
?>

<section class="band band-white">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Coming storefront</span>
            <h1>Retail is coming next.</h1>
            <p>From Wolf to Wow is preparing a storefront for grooming and daycare essentials. Watch this space for coat-care products, everyday supplies, and local pet-care offers.</p>
            <div class="button-row">
                <a class="button button-coral" href="booknow.php">Book a Service</a>
                <a class="button button-outline" href="contact.php">Ask About Products</a>
            </div>
        </div>
        <div class="ad-panel">
            <h2>Featured partner</h2>
            <iframe class="ad-iframe" src="http://192.168.1.250:8000/serve-ad.php?slot=square" width="250" height="250" loading="lazy" title="Square Ad"></iframe>
        </div>
    </div>
</section>

<section class="band band-warm">
    <div class="band-inner">
        <span class="eyebrow">Local offers</span>
        <h2>Current announcement</h2>
        <iframe class="ad-iframe" src="http://192.168.1.250:8000/serve-ad.php?slot=banner" width="690" height="96" loading="lazy" title="Rectangle Banner Ad"></iframe>
    </div>
</section>

<?php render_footer(); ?>
