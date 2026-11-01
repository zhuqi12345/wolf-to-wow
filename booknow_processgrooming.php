<?php
require_once __DIR__ . '/app.php';

$user = require_login('booknow.php?service=grooming#booking-availability');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('booknow.php?service=grooming#booking-availability');
}

$dogName = trim($_POST['dog_name'] ?? '');
$dogSize = trim($_POST['dog_size'] ?? '');
$groomerId = trim($_POST['grm_id'] ?? '');
$date = trim($_POST['appt_date'] ?? '');
$time = trim($_POST['appt_time'] ?? '');
$notes = trim($_POST['dog_notes'] ?? '');
$serviceKey = trim((string) ($_POST['service'] ?? ''));

$validServices = grooming_services();

if ($dogName === '' || !isset(size_labels()[$dogSize]) || !$groomerId || !$date || !$time || !isset($validServices[$serviceKey])) {
    set_flash('error', 'Please complete the grooming booking form.');
    $target = 'booknow.php?service=grooming&date=' . urlencode($date ?: date('Y-m-d'));
    if ($groomerId && $time) {
        $target .= '&groomer_id=' . urlencode($groomerId) . '&time=' . urlencode($time) . '#confirm-booking';
    } else {
        $target .= '#booking-availability';
    }
    redirect_to($target);
}

if (!valid_booking_date($date) || !isset(grooming_time_slots()[$time]) || !groomer_by_id($groomerId)) {
    set_flash('error', 'That grooming time is outside the booking window.');
    redirect_to('booknow.php?service=grooming#booking-availability');
}

if (grooming_slot_taken($date, $time, $groomerId)) {
    set_flash('error', 'That grooming spot was just booked. Please choose another available time.');
    redirect_to('booknow.php?service=grooming&date=' . urlencode($date) . '#booking-availability');
}

if (!groomer_week_has_capacity($date, $groomerId)) {
    set_flash('error', 'That groomer is at their weekly booking limit. Please choose another groomer or week.');
    redirect_to('booknow.php?service=grooming&date=' . urlencode($date) . '#booking-availability');
}

$serviceText = $validServices[$serviceKey]['label'];
$price = estimate_grooming_price([$serviceKey], $dogSize, $groomerId);
$status = 'pending';

$stmt = $conn->prepare(
    'INSERT INTO grmbk_groombookings
        (GRMBK_Date, GRM_ID, GRMBK_DogName, GRMBK_DogSize, GRMBK_ServicesToPerform, USR_ID, GRMBK_StartTime, GRMBK_EstimatedPrice, GRMBK_DogNotes, GRMBK_Status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('sssssssdss', $date, $groomerId, $dogName, $dogSize, $serviceText, $user['USR_ID'], $time, $price, $notes, $status);
$stmt->execute();
$stmt->close();

set_flash('success', 'Grooming request received. Staff will confirm the appointment.');
redirect_to('account.php#appointments');
?>
