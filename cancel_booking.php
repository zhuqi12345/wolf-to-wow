<?php
require_once __DIR__ . '/app.php';
$user = require_login('account.php#appointments');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('account.php#appointments');
}

$type = $_POST['type'] ?? '';
$id = trim($_POST['id'] ?? '');

if ($type === 'grooming') {
    $stmt = $conn->prepare(
        "UPDATE grmbk_groombookings
         SET GRMBK_Status = 'cancelled'
         WHERE GRMBK_ID = ?
           AND USR_ID = ?
           AND GRMBK_Date >= CURDATE()
           AND COALESCE(GRMBK_Status, 'pending') <> 'cancelled'"
    );
    $stmt->bind_param('ss', $id, $user['USR_ID']);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
} elseif ($type === 'daycare') {
    $stmt = $conn->prepare(
        "UPDATE daybk_daycarebookings
         SET DAYBK_Status = 'cancelled'
         WHERE DAYBK_ID = ?
           AND USR_ID = ?
           AND DAYBK_Date >= CURDATE()
           AND COALESCE(DAYBK_Status, 'pending') <> 'cancelled'"
    );
    $stmt->bind_param('ss', $id, $user['USR_ID']);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
} else {
    $changed = 0;
}

set_flash($changed > 0 ? 'success' : 'error', $changed > 0 ? 'Booking cancelled.' : 'That booking could not be cancelled.');
redirect_to('account.php#appointments');
?>
