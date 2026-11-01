<?php
require_once __DIR__ . '/app.php';

$user = require_login('booknow.php?service=daycare#booking-availability');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('booknow.php?service=daycare#booking-availability');
}

$dogName = trim($_POST['daybk_dog_name'] ?? '');
$dogSize = trim($_POST['daybk_dog_size'] ?? '');
$date = trim($_POST['daybk_date'] ?? '');
$period = trim($_POST['daybk_period'] ?? '');
$humanName = trim($_POST['daybk_human_name'] ?? '');
$humanPhone = trim($_POST['daybk_human_phone'] ?? '');
$humanEmail = trim($_POST['daybk_human_email'] ?? '');
$notes = trim($_POST['daybk_dog_notes'] ?? '');

if ($dogName === '' || !isset(size_labels()[$dogSize]) || $humanName === '' || $humanPhone === '' || !filter_var($humanEmail, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please complete the daycare booking form.');
    $target = 'booknow.php?service=daycare&date=' . urlencode($date ?: date('Y-m-d'));
    if ($period) {
        $target .= '&period=' . urlencode($period) . '#confirm-booking';
    } else {
        $target .= '#booking-availability';
    }
    redirect_to($target);
}

if (!valid_booking_date($date) || !isset(daycare_periods()[$period])) {
    set_flash('error', 'That daycare period is outside the booking window.');
    redirect_to('booknow.php?service=daycare#booking-availability');
}

if (daycare_spots_left($date, $period) <= 0) {
    set_flash('error', 'That daycare period is full. Please choose another available period.');
    redirect_to('booknow.php?service=daycare&date=' . urlencode($date) . '#booking-availability');
}

$price = estimate_daycare_price($period, $dogSize);
$status = 'pending';

$stmt = $conn->prepare(
    'INSERT INTO daybk_daycarebookings
        (DAYBK_Date, DAYBK_DogName, DAYBK_DogSize, DAYBK_HumanName, DAYBK_HumanPhone, DAYBK_HumanEmail, USR_ID, DAYBK_StartPeriod, DAYBK_EstimatedPrice, DAYBK_DogNotes, DAYBK_Status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssssssdss', $date, $dogName, $dogSize, $humanName, $humanPhone, $humanEmail, $user['USR_ID'], $period, $price, $notes, $status);
$stmt->execute();
$stmt->close();

set_flash('success', 'Daycare request received. Staff will confirm the appointment.');
redirect_to('account.php#appointments');
?>
