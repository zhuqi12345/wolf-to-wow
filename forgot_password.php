<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare('SELECT USR_ID, USR_FirstName FROM site_users WHERE USR_Email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $temporary = bin2hex(random_bytes(4)) . 'A!';
            $body = "Hello " . $user['USR_FirstName'] . ",\n\nYour temporary From Wolf to Wow password is: {$temporary}\n\nPlease log in and change it from your account page.";
            if (send_email($email, BUSINESS_EMAIL, 'From Wolf to Wow password reset', $body)) {
                $hash = password_hash($temporary, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE site_users SET USR_PasswordHash = ? WHERE USR_ID = ?');
                $stmt->bind_param('ss', $hash, $user['USR_ID']);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'A temporary password was emailed to you.');
                redirect_to('login.php#login-form');
            }
        }
    }
    set_flash('error', 'Password recovery email could not be sent. Please contact staff.');
    redirect_to('forgot_password.php#password-reset-form');
}

render_header('Password Recovery', '');
?>

<section class="band band-white">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Password recovery</span>
            <h1>Send a temporary password.</h1>
            <p>Enter the email on your account. If the reset email does not arrive, contact staff and they can help reset access.</p>
        </div>
        <form class="form-surface form-grid" method="post" id="password-reset-form">
            <div class="field span-2">
                <label for="email">Account email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="span-2">
                <button class="button button-coral" type="submit">Send Reset Email</button>
            </div>
        </form>
    </div>
</section>

<?php render_footer(); ?>
