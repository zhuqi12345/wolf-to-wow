<?php
require_once __DIR__ . '/layout.php';
$user = require_login('account.php#account-profile');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($address !== '' && $phone !== '') {
            $stmt = $conn->prepare('UPDATE site_users SET USR_Address = ?, USR_Phone = ? WHERE USR_ID = ?');
            $stmt->bind_param('sss', $address, $phone, $user['USR_ID']);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Profile updated.');
        }
        redirect_to('account.php#account-profile');
    }

    if ($action === 'password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($old, $user['USR_PasswordHash'])) {
            set_flash('error', 'Original password is incorrect.');
        } elseif (strlen($new) < 8 || $new !== $confirm) {
            set_flash('error', 'New passwords must match and be at least 8 characters.');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE site_users SET USR_PasswordHash = ? WHERE USR_ID = ?');
            $stmt->bind_param('ss', $hash, $user['USR_ID']);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Password updated.');
        }
        redirect_to('account.php#account-password');
    }

    if ($action === 'dog') {
        $dogName = trim($_POST['dog_name'] ?? '');
        $dogSize = trim($_POST['dog_size'] ?? '');
        $dogNotes = trim($_POST['dog_notes'] ?? '');
        if ($dogName !== '' && isset(size_labels()[$dogSize])) {
            $stmt = $conn->prepare('INSERT INTO site_dogs (USR_ID, DOG_Name, DOG_Size, DOG_Notes) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $user['USR_ID'], $dogName, $dogSize, $dogNotes);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Dog profile added.');
        }
        redirect_to('account.php#dog-profiles');
    }
}

$dogs = get_user_dogs($user['USR_ID']);

$stmt = $conn->prepare(
    "SELECT b.GRMBK_ID, b.GRMBK_Date, b.GRMBK_StartTime, b.GRMBK_DogName, b.GRMBK_DogSize,
            b.GRMBK_ServicesToPerform, b.GRMBK_EstimatedPrice, COALESCE(b.GRMBK_Status, 'pending') AS Status,
            g.GRM_GroomerName
     FROM grmbk_groombookings b
     JOIN grm_groomers g ON g.GRM_ID = b.GRM_ID
     WHERE b.USR_ID = ?
       AND b.GRMBK_Date >= CURDATE()
     ORDER BY b.GRMBK_Date ASC, b.GRMBK_StartTime ASC"
);
$stmt->bind_param('s', $user['USR_ID']);
$stmt->execute();
$groomBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare(
    "SELECT DAYBK_ID, DAYBK_Date, DAYBK_StartPeriod, DAYBK_DogName, DAYBK_DogSize,
            DAYBK_EstimatedPrice, COALESCE(DAYBK_Status, 'pending') AS Status
     FROM daybk_daycarebookings
     WHERE USR_ID = ?
       AND DAYBK_Date >= CURDATE()
     ORDER BY DAYBK_Date ASC, DAYBK_StartPeriod"
);
$stmt->bind_param('s', $user['USR_ID']);
$stmt->execute();
$daycareBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sizes = size_labels();
render_header('My Account', '');
?>

<section class="band band-white">
    <div class="band-inner">
        <span class="eyebrow">My account</span>
        <h1><?= e($user['USR_FirstName']) ?>, manage your bookings.</h1>
        <p class="text-measure">Use this page to view appointment requests, cancel bookings, update contact details, change your password, and keep dog profiles ready for the next booking.</p>

        <div class="account-grid">
            <div class="account-panel" id="account-profile">
                <h2>Profile</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="profile">
                    <div class="field span-2">
                        <label>Name</label>
                        <input type="text" value="<?= e($user['USR_FirstName'] . ' ' . $user['USR_LastName']) ?>" disabled>
                    </div>
                    <div class="field span-2">
                        <label>Email</label>
                        <input type="email" value="<?= e($user['USR_Email']) ?>" disabled>
                    </div>
                    <div class="field span-2">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?= e($user['USR_Address']) ?>" required>
                    </div>
                    <div class="field span-2">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($user['USR_Phone']) ?>" required>
                    </div>
                    <div class="span-2">
                        <button class="button" type="submit">Update Profile</button>
                    </div>
                </form>

                <h2 id="account-password">Change password</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="password">
                    <div class="field span-2">
                        <label for="old_password">Original password</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    <div class="field span-2">
                        <label for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" required>
                    </div>
                    <div class="field span-2">
                        <label for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                    <div class="span-2">
                        <button class="button" type="submit">Change Password</button>
                    </div>
                </form>
            </div>

            <div class="account-panel" id="dog-profiles">
                <h2>Dog profiles</h2>
                <?php foreach ($dogs as $dog): ?>
                    <div class="booking-row">
                        <div>
                            <strong><?= e($dog['DOG_Name']) ?></strong>
                            <p><?= e($sizes[$dog['DOG_Size']] ?? $dog['DOG_Size']) ?><?= $dog['DOG_Notes'] ? ' - ' . e($dog['DOG_Notes']) : '' ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$dogs): ?>
                    <p>No saved dogs yet.</p>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="dog">
                    <div class="field">
                        <label for="dog_name">Dog name</label>
                        <input type="text" id="dog_name" name="dog_name" maxlength="50" required>
                    </div>
                    <div class="field">
                        <label for="dog_size">Size</label>
                        <select id="dog_size" name="dog_size" required>
                            <?php foreach ($sizes as $sizeKey => $label): ?>
                                <option value="<?= e($sizeKey) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field span-2">
                        <label for="dog_notes">Notes</label>
                        <textarea id="dog_notes" name="dog_notes" maxlength="500"></textarea>
                    </div>
                    <div class="span-2">
                        <button class="button" type="submit">Add Dog</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="band band-warm" id="appointments">
    <div class="band-inner split">
        <div>
            <span class="eyebrow">Appointments</span>
            <h2>Upcoming booking requests.</h2>
            <p>Only today and future bookings are shown here. Staff still reviews pending requests before final confirmation.</p>
        </div>
        <div class="account-panel">
            <h3>Grooming</h3>
            <?php foreach ($groomBookings as $booking): ?>
                <div class="booking-row">
                    <div>
                        <span class="status <?= e($booking['Status']) ?>"><?= e(booking_status_label($booking['Status'])) ?></span>
                        <p><strong><?= e(pretty_date($booking['GRMBK_Date'])) ?></strong> at <?= e(substr($booking['GRMBK_StartTime'], 0, 5)) ?> with <?= e($booking['GRM_GroomerName']) ?></p>
                        <p><?= e($booking['GRMBK_DogName']) ?> - <?= e($booking['GRMBK_ServicesToPerform']) ?> - <?= e(money((float) $booking['GRMBK_EstimatedPrice'])) ?></p>
                    </div>
                    <?php if ($booking['Status'] !== 'cancelled' && $booking['GRMBK_Date'] >= date('Y-m-d')): ?>
                        <form action="cancel_booking.php" method="post">
                            <input type="hidden" name="type" value="grooming">
                            <input type="hidden" name="id" value="<?= e($booking['GRMBK_ID']) ?>">
                            <button class="button button-outline" type="submit">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$groomBookings): ?><p>No upcoming grooming bookings.</p><?php endif; ?>

            <h3>Daycare</h3>
            <?php foreach ($daycareBookings as $booking): ?>
                <div class="booking-row">
                    <div>
                        <span class="status <?= e($booking['Status']) ?>"><?= e(booking_status_label($booking['Status'])) ?></span>
                        <p><strong><?= e(pretty_date($booking['DAYBK_Date'])) ?></strong> - <?= e(daycare_periods()[$booking['DAYBK_StartPeriod']]['label'] ?? $booking['DAYBK_StartPeriod']) ?></p>
                        <p><?= e($booking['DAYBK_DogName']) ?> - <?= e($sizes[$booking['DAYBK_DogSize']] ?? $booking['DAYBK_DogSize']) ?> - <?= e(money((float) $booking['DAYBK_EstimatedPrice'])) ?></p>
                    </div>
                    <?php if ($booking['Status'] !== 'cancelled' && $booking['DAYBK_Date'] >= date('Y-m-d')): ?>
                        <form action="cancel_booking.php" method="post">
                            <input type="hidden" name="type" value="daycare">
                            <input type="hidden" name="id" value="<?= e($booking['DAYBK_ID']) ?>">
                            <button class="button button-outline" type="submit">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$daycareBookings): ?><p>No upcoming daycare bookings.</p><?php endif; ?>
        </div>
    </div>
</section>

<?php render_footer(); ?>
