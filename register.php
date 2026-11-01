<?php
require_once __DIR__ . '/layout.php';

$return = $_GET['return'] ?? 'account.php#account-profile';
if (preg_match('/^https?:\/\//i', $return)) {
    $return = 'account.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $return = $_POST['return'] ?? 'account.php#account-profile';

    if ($first === '' || $last === '' || $address === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $confirm) {
        set_flash('error', 'Please complete registration with a valid email and matching password of at least 8 characters.');
        redirect_to('register.php?return=' . urlencode($return) . '#register-form');
    }

    $stmt = $conn->prepare('SELECT USR_ID FROM site_users WHERE USR_Email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($exists) {
        set_flash('error', 'An account already exists for that email.');
        redirect_to('login.php?return=' . urlencode($return) . '#login-form');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $isAdmin = 0;
    $stmt = $conn->prepare(
        'INSERT INTO site_users
            (USR_FirstName, USR_LastName, USR_Address, USR_Phone, USR_Email, USR_PasswordHash, USR_IsAdmin)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssssi', $first, $last, $address, $phone, $email, $hash, $isAdmin);
    $stmt->execute();
    $userId = $conn->insert_id ?: '';
    $stmt->close();

    if (!$userId) {
        $stmt = $conn->prepare('SELECT USR_ID FROM site_users WHERE USR_Email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $userId = $stmt->get_result()->fetch_assoc()['USR_ID'] ?? '';
        $stmt->close();
    }

    $dogNames = $_POST['dog_name'] ?? [];
    $dogSizes = $_POST['dog_size'] ?? [];
    $dogNotes = $_POST['dog_notes'] ?? [];
    for ($i = 0; $i < count($dogNames); $i++) {
        $dogName = trim((string) ($dogNames[$i] ?? ''));
        $dogSize = trim((string) ($dogSizes[$i] ?? ''));
        $dogNote = trim((string) ($dogNotes[$i] ?? ''));
        if ($dogName === '' || !isset(size_labels()[$dogSize])) {
            continue;
        }
        $stmt = $conn->prepare('INSERT INTO site_dogs (USR_ID, DOG_Name, DOG_Size, DOG_Notes) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $userId, $dogName, $dogSize, $dogNote);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['user_id'] = $userId;
    set_flash('success', 'Account created. You can now book and manage appointments.');
    redirect_to($return ?: 'account.php#account-profile');
}

$sizes = size_labels();
render_header('Register', '');
?>

<section class="band band-white">
    <div class="band-inner">
        <span class="eyebrow">Owner and pet registration</span>
        <h1>Create your client account.</h1>
        <p class="text-measure">Saved dog details make future grooming and daycare bookings faster. You can add more dogs later from your account.</p>
        <form class="form-surface form-grid" action="register.php" method="post" id="register-form">
            <input type="hidden" name="return" value="<?= e($return) ?>">
            <div class="field">
                <label for="first_name">First name</label>
                <input type="text" id="first_name" name="first_name" maxlength="50" required>
            </div>
            <div class="field">
                <label for="last_name">Last name</label>
                <input type="text" id="last_name" name="last_name" maxlength="50" required>
            </div>
            <div class="field span-2">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" maxlength="160" required>
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" maxlength="20" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="100" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>

            <div class="span-2">
                <h2>Optional dog profiles</h2>
                <div class="daycare-grid">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="period-card">
                            <div class="field">
                                <label>Dog name</label>
                                <input type="text" name="dog_name[]" maxlength="50">
                            </div>
                            <div class="field">
                                <label>Size</label>
                                <select name="dog_size[]">
                                    <option value="">Choose size</option>
                                    <?php foreach ($sizes as $sizeKey => $label): ?>
                                        <option value="<?= e($sizeKey) ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label>Notes</label>
                                <textarea name="dog_notes[]" maxlength="500"></textarea>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="span-2">
                <button class="button button-coral" type="submit">Create Account</button>
            </div>
        </form>
    </div>
</section>

<?php render_footer(); ?>
