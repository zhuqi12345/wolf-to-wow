<?php
require_once __DIR__ . '/layout.php';

$return = $_GET['return'] ?? 'account.php#account-profile';
if (preg_match('/^https?:\/\//i', $return)) {
    $return = 'account.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $return = $_POST['return'] ?? 'account.php#account-profile';

    if (authenticate_user($email, $password)) {
        redirect_to($return ?: 'account.php#account-profile');
    }

    set_flash('error', 'Email or password is incorrect.');
    redirect_to('login.php?return=' . urlencode($return) . '#login-form');
}

render_header('Log In', '');
?>

<section class="band band-white">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Client account</span>
            <h1>Log in to book or manage appointments.</h1>
            <p>Customers can save dog details, request bookings, view upcoming appointments, and cancel appointments from the account area.</p>
        </div>
        <form class="form-surface form-grid" action="login.php" method="post" id="login-form">
            <input type="hidden" name="return" value="<?= e($return) ?>">
            <div class="field span-2">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="field span-2">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <a class="form-help-link" href="forgot_password.php#password-reset-form">Forgot your password?</a>
            </div>
            <div class="span-2 button-row">
                <button class="button button-coral" type="submit">Log In</button>
                <a class="button button-outline" href="register.php?return=<?= e(urlencode($return)) ?>#register-form">Create Account</a>
            </div>
        </form>
    </div>
</section>

<?php render_footer(); ?>
