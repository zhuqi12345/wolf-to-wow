<?php
require_once __DIR__ . '/layout.php';
$user = require_login('staff.php');
if ((int) $user['USR_IsAdmin'] !== 1) {
    set_flash('error', 'Staff access is required.');
    redirect_to('account.php#appointments');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $id = trim((string) ($_POST['id'] ?? ''));
    $status = $_POST['status'] ?? '';
    $redirectTarget = $type === 'daycare' ? 'staff.php#staff-daycare' : 'staff.php#staff-grooming';

    if (!in_array($type, ['grooming', 'daycare'], true) || $id === '') {
        set_flash('error', 'That booking could not be updated.');
        redirect_to($redirectTarget);
    }

    if (!in_array($status, ['approved', 'cancelled'], true)) {
        set_flash('error', 'Choose Confirm or Cancel for a pending booking.');
        redirect_to($redirectTarget);
    }

    $approved = $status === 'approved' ? 1 : 0;
    $changed = 0;

    if ($type === 'grooming') {
        $stmt = $conn->prepare(
            "UPDATE grmbk_groombookings
             SET GRMBK_Status = ?, GRMBK_Approved = ?
             WHERE GRMBK_ID = ?
               AND COALESCE(GRMBK_Status, 'pending') = 'pending'"
        );
        $stmt->bind_param('sis', $status, $approved, $id);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
    } elseif ($type === 'daycare') {
        $stmt = $conn->prepare(
            "UPDATE daybk_daycarebookings
             SET DAYBK_Status = ?, DAYBK_Approved = ?
             WHERE DAYBK_ID = ?
               AND COALESCE(DAYBK_Status, 'pending') = 'pending'"
        );
        $stmt->bind_param('sis', $status, $approved, $id);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
    }

    set_flash(
        $changed > 0 ? 'success' : 'error',
        $changed > 0 ? 'Booking status updated.' : 'Only pending bookings can be confirmed or cancelled.'
    );
    redirect_to($redirectTarget);
}

$groomBookings = $conn->query(
    "SELECT b.GRMBK_ID, b.GRMBK_Date, b.GRMBK_StartTime, b.GRMBK_DogName, b.GRMBK_DogSize,
            b.GRMBK_ServicesToPerform, b.GRMBK_EstimatedPrice, COALESCE(b.GRMBK_Status, 'pending') AS Status,
            g.GRM_GroomerName, u.USR_FirstName, u.USR_LastName, u.USR_Email, u.USR_Phone
     FROM grmbk_groombookings b
     JOIN grm_groomers g ON g.GRM_ID = b.GRM_ID
     LEFT JOIN site_users u ON u.USR_ID = b.USR_ID
     WHERE b.GRMBK_Date >= CURDATE()
     ORDER BY b.GRMBK_Date ASC, b.GRMBK_StartTime ASC"
)->fetch_all(MYSQLI_ASSOC);

$daycareBookings = $conn->query(
    "SELECT d.DAYBK_ID, d.DAYBK_Date, d.DAYBK_StartPeriod, d.DAYBK_DogName, d.DAYBK_DogSize,
            d.DAYBK_EstimatedPrice, COALESCE(d.DAYBK_Status, 'pending') AS Status,
            d.DAYBK_HumanName, d.DAYBK_HumanPhone, d.DAYBK_HumanEmail
     FROM daybk_daycarebookings d
     WHERE d.DAYBK_Date >= CURDATE()
     ORDER BY d.DAYBK_Date ASC, d.DAYBK_StartPeriod"
)->fetch_all(MYSQLI_ASSOC);

$staffActions = [
    'approved' => 'Confirm',
    'cancelled' => 'Cancel',
];

render_header('Staff Bookings', '');
?>

<section class="band band-white">
    <div class="band-inner">
        <span class="eyebrow">Staff dashboard</span>
        <h1>View and confirm bookings.</h1>
        <p class="text-measure">Review today and future grooming and daycare requests in one place. Staff can confirm or cancel pending requests only. Confirmed and cancelled bookings are final on this dashboard.</p>

        <div class="staff-table-wrap" id="staff-grooming">
            <h2>Grooming bookings</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Groomer</th>
                        <th>Dog</th>
                        <th>Services</th>
                        <th>Customer</th>
                        <th>Estimate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groomBookings as $booking): ?>
                        <?php $bookingStatus = $booking['Status'] ?: 'pending'; ?>
                        <tr>
                            <td><?= e($booking['GRMBK_Date']) ?></td>
                            <td><?= e(substr((string) $booking['GRMBK_StartTime'], 0, 5)) ?></td>
                            <td><?= e($booking['GRM_GroomerName']) ?></td>
                            <td><?= e($booking['GRMBK_DogName']) ?></td>
                            <td><?= e($booking['GRMBK_ServicesToPerform']) ?></td>
                            <td><?= e(trim(($booking['USR_FirstName'] ?? '') . ' ' . ($booking['USR_LastName'] ?? ''))) ?><br><?= e($booking['USR_Email'] ?? '') ?></td>
                            <td><?= e(money((float) $booking['GRMBK_EstimatedPrice'])) ?></td>
                            <td>
                                <div class="staff-status-action">
                                    <span class="status <?= e($bookingStatus) ?>"><?= e(booking_status_label($bookingStatus)) ?></span>
                                    <?php if ($bookingStatus === 'pending'): ?>
                                        <form method="post" class="inline-status-form">
                                            <input type="hidden" name="type" value="grooming">
                                            <input type="hidden" name="id" value="<?= e($booking['GRMBK_ID']) ?>">
                                            <select name="status" aria-label="Grooming booking action" required>
                                                <option value="" selected disabled>Choose action</option>
                                                <?php foreach ($staffActions as $actionStatus => $actionLabel): ?>
                                                    <option value="<?= e($actionStatus) ?>"><?= e($actionLabel) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="button button-outline" type="submit">Save</button>
                                        </form>
                                    <?php else: ?>
                                        <small class="locked-note">Final status</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="staff-table-wrap" id="staff-daycare">
            <h2>Daycare bookings</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Period</th>
                        <th>Dog</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Estimate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daycareBookings as $booking): ?>
                        <?php $bookingStatus = $booking['Status'] ?: 'pending'; ?>
                        <tr>
                            <td><?= e($booking['DAYBK_Date']) ?></td>
                            <td><?= e(daycare_periods()[$booking['DAYBK_StartPeriod']]['label'] ?? $booking['DAYBK_StartPeriod']) ?></td>
                            <td><?= e($booking['DAYBK_DogName']) ?></td>
                            <td><?= e($booking['DAYBK_HumanName']) ?><br><?= e($booking['DAYBK_HumanEmail']) ?></td>
                            <td><?= e($booking['DAYBK_HumanPhone']) ?></td>
                            <td><?= e(money((float) $booking['DAYBK_EstimatedPrice'])) ?></td>
                            <td>
                                <div class="staff-status-action">
                                    <span class="status <?= e($bookingStatus) ?>"><?= e(booking_status_label($bookingStatus)) ?></span>
                                    <?php if ($bookingStatus === 'pending'): ?>
                                        <form method="post" class="inline-status-form">
                                            <input type="hidden" name="type" value="daycare">
                                            <input type="hidden" name="id" value="<?= e($booking['DAYBK_ID']) ?>">
                                            <select name="status" aria-label="Daycare booking action" required>
                                                <option value="" selected disabled>Choose action</option>
                                                <?php foreach ($staffActions as $actionStatus => $actionLabel): ?>
                                                    <option value="<?= e($actionStatus) ?>"><?= e($actionLabel) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="button button-outline" type="submit">Save</button>
                                        </form>
                                    <?php else: ?>
                                        <small class="locked-note">Final status</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php render_footer(); ?>
