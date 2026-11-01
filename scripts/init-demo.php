<?php
// Deliberately CLI-only: a web request must never provision demo accounts.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this script from the command line.');
}
if (!in_array('--demo', $argv, true)) {
    fwrite(STDERR, "Usage: php scripts/init-demo.php --demo\n");
    exit(1);
}

$config = require dirname(__DIR__) . '/config.php';
$databaseName = (string) $config['db_name'];
// Never seed the original web_gpt database or an arbitrary production database.
if (!preg_match('/\A[a-zA-Z0-9_]+_demo\z/', $databaseName)) {
    fwrite(STDERR, "Demo initialization requires a database name ending in _demo.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$server = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], '', $config['db_port']);
$server->query("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$server->close();

require dirname(__DIR__) . '/app.php';

function insert_demo_row(mysqli $conn, string $table, array $values): void
{
    // Identifiers are internal constants below, never user input.
    $columns = implode(', ', array_keys($values));
    $placeholders = implode(', ', array_fill(0, count($values), '?'));
    $stmt = $conn->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
    $stmt->execute(array_values($values));
    $stmt->close();
}

function demo_user(mysqli $conn, string $email, string $first, string $last, int $isAdmin): string
{
    $stmt = $conn->prepare('SELECT USR_ID FROM site_users WHERE USR_Email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($existing) {
        // Re-running setup never changes an existing user's password or role.
        return $existing['USR_ID'];
    }
    $id = $conn->query('SELECT UUID() AS id')->fetch_assoc()['id'];
    insert_demo_row($conn, 'site_users', [
        'USR_ID' => $id,
        'USR_FirstName' => $first,
        'USR_LastName' => $last,
        'USR_Address' => '123 Demo Street, Edmonton AB',
        'USR_Phone' => '7805550100',
        'USR_Email' => $email,
        'USR_PasswordHash' => password_hash('PortfolioDemo123!', PASSWORD_DEFAULT),
        'USR_IsAdmin' => $isAdmin,
    ]);
    return $id;
}

$conn->begin_transaction();
try {
    demo_user($conn, 'staff@example.com', 'Demo', 'Staff', 1);
    $customerId = demo_user($conn, 'customer@example.com', 'Alex', 'Demo', 0);

    $stmt = $conn->prepare('SELECT DOG_ID FROM site_dogs WHERE USR_ID = ? LIMIT 1');
    $stmt->bind_param('s', $customerId);
    $stmt->execute();
    $hasDog = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$hasDog) {
        insert_demo_row($conn, 'site_dogs', [
            'USR_ID' => $customerId,
            'DOG_Name' => 'Mochi',
            'DOG_Size' => 'M',
            'DOG_Notes' => 'Fictional portfolio pet. Enjoys a calm introduction.',
        ]);
    }

    $marker = 'Fictional portfolio sample';
    $date = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
    while (!valid_booking_date($date)) {
        $date = (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    }
    $groomer = get_groomers()[0];
    $stmt = $conn->prepare('SELECT GRMBK_ID FROM grmbk_groombookings WHERE USR_ID = ? AND GRMBK_DogNotes = ? LIMIT 1');
    $stmt->bind_param('ss', $customerId, $marker);
    $stmt->execute();
    $hasGrooming = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$hasGrooming && grooming_slot_available($date, '09:00:00', $groomer['GRM_ID'])) {
        insert_demo_row($conn, 'grmbk_groombookings', [
            'GRMBK_Date' => $date,
            'GRM_ID' => $groomer['GRM_ID'],
            'GRMBK_DogName' => 'Mochi',
            'GRMBK_DogSize' => 'M',
            'GRMBK_ServicesToPerform' => 'Bath & Brush',
            'USR_ID' => $customerId,
            'GRMBK_StartTime' => '09:00:00',
            'GRMBK_EstimatedPrice' => estimate_grooming_price(['bath_brush'], 'M', $groomer['GRM_ID']),
            'GRMBK_DogNotes' => $marker,
            'GRMBK_Status' => 'pending',
        ]);
    }
    $stmt = $conn->prepare('SELECT DAYBK_ID FROM daybk_daycarebookings WHERE USR_ID = ? AND DAYBK_DogNotes = ? LIMIT 1');
    $stmt->bind_param('ss', $customerId, $marker);
    $stmt->execute();
    $hasDaycare = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$hasDaycare && daycare_spots_left($date, 'MORNING') > 0) {
        insert_demo_row($conn, 'daybk_daycarebookings', [
            'DAYBK_Date' => $date,
            'DAYBK_DogName' => 'Mochi',
            'DAYBK_DogSize' => 'M',
            'DAYBK_HumanName' => 'Alex Demo',
            'DAYBK_HumanPhone' => '7805550100',
            'DAYBK_HumanEmail' => 'customer@example.com',
            'USR_ID' => $customerId,
            'DAYBK_StartPeriod' => 'MORNING',
            'DAYBK_EstimatedPrice' => estimate_daycare_price('MORNING', 'M'),
            'DAYBK_DogNotes' => $marker,
            'DAYBK_Status' => 'pending',
        ]);
    }
    $conn->commit();
} catch (Throwable $exception) {
    $conn->rollback();
    throw $exception;
}
echo "Demo database ready: {$databaseName}\n";
echo "Customer: customer@example.com\nStaff: staff@example.com\n";
echo "Initial demo password (both accounts): PortfolioDemo123!\n";
echo "Existing passwords and bookings are preserved on repeat runs.\n";
