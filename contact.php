<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $name = trim($first . ' ' . $last);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') {
        set_flash('error', 'Please complete the required contact fields.');
        redirect_to('contact.php#contact-form');
    }

    $body = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n{$message}";
    $sent = send_email(BUSINESS_EMAIL, $email, $subject, $body) ? 1 : 0;

    $stmt = $conn->prepare(
        'INSERT INTO site_contactmessages (MSG_Name, MSG_Email, MSG_Phone, MSG_Subject, MSG_Body, MSG_EmailSent)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssssi', $name, $email, $phone, $subject, $message, $sent);
    $stmt->execute();
    $stmt->close();

    set_flash('success', $sent ? 'Message sent. We will respond within one business day.' : 'Message saved. Please call if your request is time-sensitive.');
    redirect_to('contact.php#contact-form');
}

$currentUser = current_user();
$contactFirstName = $currentUser['USR_FirstName'] ?? '';
$contactLastName = $currentUser['USR_LastName'] ?? '';
$contactPhone = $currentUser['USR_Phone'] ?? '';
$contactEmail = $currentUser['USR_Email'] ?? '';

render_header('Contact Us', 'contact');
?>

<section class="band band-white">
    <div class="band-inner contact-grid">
        <div>
            <span class="eyebrow">Contact</span>
            <h1>Questions before you book?</h1>
            <p>Use the form for service questions, daycare fit, grooming concerns, or booking help. Official booking changes should be made through your account whenever possible.</p>
            <div class="info-list">
                <div>
                    <h3 class="heading-icon"><?= site_icon('map-pin') ?><span>Address</span></h3>
                    <p><?= e(BUSINESS_ADDRESS) ?></p>
                </div>
                <div>
                    <h3 class="heading-icon"><?= site_icon('phone') ?><span>Phone</span></h3>
                    <p><a href="tel:<?= e(BUSINESS_PHONE) ?>">667.897.9999</a></p>
                </div>
                <div>
                    <h3 class="heading-icon"><?= site_icon('mail') ?><span>Email</span></h3>
                    <p><a href="mailto:<?= e(BUSINESS_EMAIL) ?>"><?= e(BUSINESS_EMAIL) ?></a></p>
                </div>
                <div>
                    <h3 class="heading-icon"><?= site_icon('share') ?><span>Social</span></h3>
                    <p>Follow shop updates on Facebook, Instagram, and X.</p>
                </div>
            </div>
        </div>

        <form class="form-surface form-grid" method="post" id="contact-form">
            <div class="field">
                <label for="first_name">First name</label>
                <input type="text" id="first_name" name="first_name" value="<?= e($contactFirstName) ?>" required>
            </div>
            <div class="field">
                <label for="last_name">Last name</label>
                <input type="text" id="last_name" name="last_name" value="<?= e($contactLastName) ?>" required>
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?= e($contactPhone) ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($contactEmail) ?>" required>
            </div>
            <div class="field span-2">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="field span-2">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div class="span-2">
                <button class="button button-coral" type="submit">Submit</button>
            </div>
        </form>
    </div>
</section>

<section class="band band-warm">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Visit details</span>
            <h2>Plan the handoff.</h2>
            <p>Daycare drop-off and pickup should happen during lobby hours. Grooming visits are scheduled by start time so each groomer can work safely with one dog at a time.</p>
        </div>
        <div class="info-panel">
            <h3>Response expectations</h3>
            <p>Use booking pages for appointment requests and cancellations. Use this form for service questions, handling notes, daycare fit, or anything that needs a staff reply.</p>
            <p>Messages are saved even when the local email test service is not running.</p>
        </div>
    </div>
</section>

<?php render_footer(); ?>
