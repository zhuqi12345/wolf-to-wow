<?php
date_default_timezone_set('America/Edmonton');

if (session_status() === PHP_SESSION_NONE) {
    $sessionConfig = require __DIR__ . '/config.php';
    session_name($sessionConfig['session_name']);
    $sessionPath = __DIR__ . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0775, true);
    }
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
    session_start();
}

require_once __DIR__ . '/db_connect.php';

const DAYCARE_CAPACITY = 16;
const GROOMING_BOOKING_MINUTES = 90;
const BUSINESS_EMAIL = 'info@wolftowow.ca';
const BUSINESS_PHONE = '6678979999';
const BUSINESS_ADDRESS = '123 Fake Street, Edmonton AB T5A 1B4';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function table_exists(mysqli $conn, string $table): bool
{
    $safe = $conn->real_escape_string($table);
    $result = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $result && $result->num_rows > 0;
}

function column_exists(mysqli $conn, string $table, string $column): bool
{
    $safeTable = str_replace('`', '``', $table);
    $safeColumn = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");
    return $result && $result->num_rows > 0;
}

function add_column_if_missing(mysqli $conn, string $table, string $column, string $definition): void
{
    if (!column_exists($conn, $table, $column)) {
        $safeTable = str_replace('`', '``', $table);
        $safeColumn = str_replace('`', '``', $column);
        $conn->query("ALTER TABLE `{$safeTable}` ADD COLUMN `{$safeColumn}` {$definition}");
    }
}

function ensure_site_schema(mysqli $conn): void
{
    $conn->query(
        "CREATE TABLE IF NOT EXISTS grm_groomers (
            GRM_ID CHAR(36) NOT NULL DEFAULT (UUID()),
            GRM_GroomerName VARCHAR(50) NOT NULL,
            GRM_HoursPerWeek INT NOT NULL,
            CONSTRAINT PK_grm_groomers PRIMARY KEY (GRM_ID)
        )"
    );

    $countResult = $conn->query('SELECT COUNT(*) AS total FROM grm_groomers');
    $groomerCount = (int) ($countResult->fetch_assoc()['total'] ?? 0);
    if ($groomerCount === 0) {
        $stmt = $conn->prepare('INSERT INTO grm_groomers (GRM_GroomerName, GRM_HoursPerWeek) VALUES (?, ?)');
        foreach ([
            ['Sarah Mitchell', 40],
            ['Jake Thornton', 32],
            ['Priya Patel', 40],
            ['Cody Brennan', 24],
            ['Linda Kowalski', 36],
        ] as [$name, $hours]) {
            $stmt->bind_param('si', $name, $hours);
            $stmt->execute();
        }
        $stmt->close();
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS grmbk_groombookings (
            GRMBK_ID CHAR(36) NOT NULL DEFAULT (UUID()),
            GRMBK_Date DATE NOT NULL,
            GRM_ID CHAR(36) NOT NULL,
            GRMBK_DogName VARCHAR(50) NOT NULL,
            GRMBK_DogSize CHAR(3) NOT NULL,
            GRMBK_ServicesToPerform VARCHAR(500) NOT NULL,
            GRMBK_Approved BIT(1) NOT NULL DEFAULT b'0',
            CONSTRAINT PK_grmbk_groombookings PRIMARY KEY (GRMBK_ID),
            CONSTRAINT FK_GRMBK_Groomer FOREIGN KEY (GRM_ID)
                REFERENCES grm_groomers(GRM_ID),
            CONSTRAINT CHK_GRMBK_DogSize CHECK (GRMBK_DogSize IN ('S', 'M', 'L', 'XL', 'XXL'))
        )"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS daybk_daycarebookings (
            DAYBK_ID CHAR(36) NOT NULL DEFAULT (UUID()),
            DAYBK_Date DATE NOT NULL,
            DAYBK_DogName VARCHAR(50) NOT NULL,
            DAYBK_DogSize CHAR(3) NOT NULL,
            DAYBK_HumanName VARCHAR(50) NOT NULL,
            DAYBK_HumanPhone VARCHAR(10) NOT NULL,
            DAYBK_HumanEmail VARCHAR(50) NOT NULL,
            DAYBK_Approved BIT(1) NOT NULL DEFAULT b'0',
            CONSTRAINT PK_daybk_daycarebookings PRIMARY KEY (DAYBK_ID),
            CONSTRAINT CHK_DAYBK_DogSize CHECK (DAYBK_DogSize IN ('S', 'M', 'L', 'XL', 'XXL'))
        )"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS site_users (
            USR_ID CHAR(36) NOT NULL DEFAULT (UUID()),
            USR_FirstName VARCHAR(50) NOT NULL,
            USR_LastName VARCHAR(50) NOT NULL,
            USR_Address VARCHAR(160) NOT NULL,
            USR_Phone VARCHAR(20) NOT NULL,
            USR_Email VARCHAR(100) NOT NULL,
            USR_PasswordHash VARCHAR(255) NOT NULL,
            USR_IsAdmin TINYINT(1) NOT NULL DEFAULT 0,
            USR_CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT PK_site_users PRIMARY KEY (USR_ID),
            CONSTRAINT UQ_site_users_Email UNIQUE (USR_Email)
        )"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS site_dogs (
            DOG_ID CHAR(36) NOT NULL DEFAULT (UUID()),
            USR_ID CHAR(36) NOT NULL,
            DOG_Name VARCHAR(50) NOT NULL,
            DOG_Size CHAR(3) NOT NULL,
            DOG_Notes VARCHAR(500) NULL,
            DOG_CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT PK_site_dogs PRIMARY KEY (DOG_ID),
            INDEX IX_site_dogs_User (USR_ID)
        )"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS site_contactmessages (
            MSG_ID CHAR(36) NOT NULL DEFAULT (UUID()),
            MSG_Name VARCHAR(100) NOT NULL,
            MSG_Email VARCHAR(100) NOT NULL,
            MSG_Phone VARCHAR(20) NULL,
            MSG_Subject VARCHAR(120) NOT NULL,
            MSG_Body TEXT NOT NULL,
            MSG_EmailSent TINYINT(1) NOT NULL DEFAULT 0,
            MSG_CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT PK_site_contactmessages PRIMARY KEY (MSG_ID)
        )"
    );

    add_column_if_missing($conn, 'grmbk_groombookings', 'USR_ID', 'CHAR(36) NULL');
    add_column_if_missing($conn, 'grmbk_groombookings', 'GRMBK_StartTime', 'TIME NULL');
    add_column_if_missing($conn, 'grmbk_groombookings', 'GRMBK_EstimatedPrice', 'DECIMAL(8,2) NULL');
    add_column_if_missing($conn, 'grmbk_groombookings', 'GRMBK_DogNotes', 'VARCHAR(500) NULL');
    add_column_if_missing($conn, 'grmbk_groombookings', 'GRMBK_Status', "VARCHAR(20) NOT NULL DEFAULT 'pending'");
    add_column_if_missing($conn, 'grmbk_groombookings', 'GRMBK_CreatedAt', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

    add_column_if_missing($conn, 'daybk_daycarebookings', 'USR_ID', 'CHAR(36) NULL');
    add_column_if_missing($conn, 'daybk_daycarebookings', 'DAYBK_StartPeriod', "VARCHAR(20) NOT NULL DEFAULT 'FULL'");
    add_column_if_missing($conn, 'daybk_daycarebookings', 'DAYBK_EstimatedPrice', 'DECIMAL(8,2) NULL');
    add_column_if_missing($conn, 'daybk_daycarebookings', 'DAYBK_DogNotes', 'VARCHAR(500) NULL');
    add_column_if_missing($conn, 'daybk_daycarebookings', 'DAYBK_Status', "VARCHAR(20) NOT NULL DEFAULT 'pending'");
    add_column_if_missing($conn, 'daybk_daycarebookings', 'DAYBK_CreatedAt', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

    try {
        $conn->query('ALTER TABLE daybk_daycarebookings MODIFY DAYBK_HumanPhone VARCHAR(20) NOT NULL');
        $conn->query('ALTER TABLE daybk_daycarebookings MODIFY DAYBK_HumanEmail VARCHAR(100) NOT NULL');
    } catch (mysqli_sql_exception $exception) {
        // Existing data or older MariaDB builds may reject a no-op modify; the site can still run.
    }

    // Accounts are provisioned explicitly by scripts/init-demo.php or registration.
}

ensure_site_schema($conn);

function size_labels(): array
{
    return [
        'S' => 'Small',
        'M' => 'Medium',
        'L' => 'Large',
        'XL' => 'XL',
        'XXL' => 'XXL',
    ];
}

function grooming_services(): array
{
    return [
        'bath_brush' => [
            'label' => 'Bath & Brush',
            'summary' => 'A deep wash, coat-safe conditioning, blow dry, and brush-out for routine coat care.',
            'rates' => ['S' => 25, 'M' => 35, 'L' => 50, 'XL' => 65, 'XXL' => 80],
        ],
        'full_groom' => [
            'label' => 'Full Groom',
            'summary' => 'Bath, brush, breed-aware haircut, tidy face, feet, sanitary trim, and finishing detail.',
            'rates' => ['S' => 45, 'M' => 60, 'L' => 80, 'XL' => 100, 'XXL' => 125],
        ],
        'nail_trim' => [
            'label' => 'Nail Trim & File',
            'summary' => 'Safe nail clipping and filing to keep paws comfortable between full appointments.',
            'rates' => ['S' => 15, 'M' => 18, 'L' => 22, 'XL' => 25, 'XXL' => 30],
        ],
        'teeth_brushing' => [
            'label' => 'Teeth Brushing',
            'summary' => 'A gentle freshening service using dog-safe toothpaste.',
            'rates' => ['S' => 12, 'M' => 15, 'L' => 18, 'XL' => 20, 'XXL' => 25],
        ],
        'flea_tick' => [
            'label' => 'Flea & Tick Treatment Bath',
            'summary' => 'A seasonal treatment bath for dogs coming back from hikes, parks, or camping.',
            'rates' => ['S' => 35, 'M' => 50, 'L' => 65, 'XL' => 80, 'XXL' => 95],
        ],
    ];
}

function daycare_periods(): array
{
    return [
        'MORNING' => ['label' => 'Morning', 'time' => '7:30 AM - 12:00 PM', 'hours' => 4.5],
        'AFTERNOON' => ['label' => 'Afternoon', 'time' => '12:30 PM - 6:00 PM', 'hours' => 5.5],
        'FULL' => ['label' => 'Full Day', 'time' => '7:30 AM - 6:00 PM', 'hours' => 10.5],
    ];
}

function daycare_rates(): array
{
    return [
        'MORNING' => ['S' => 22, 'M' => 28, 'L' => 34, 'XL' => 40, 'XXL' => 46],
        'AFTERNOON' => ['S' => 24, 'M' => 30, 'L' => 36, 'XL' => 42, 'XXL' => 48],
        'FULL' => ['S' => 35, 'M' => 45, 'L' => 55, 'XL' => 65, 'XXL' => 75],
    ];
}

function grooming_time_slots(): array
{
    return [
        '09:00:00' => '9:00 AM',
        '10:30:00' => '10:30 AM',
        '12:00:00' => '12:00 PM',
        '13:30:00' => '1:30 PM',
        '15:00:00' => '3:00 PM',
    ];
}

function is_weekend(string $date): bool
{
    $day = (int) date('N', strtotime($date));
    return $day >= 6;
}

function date_window(): array
{
    $today = new DateTimeImmutable('today');
    return [$today, $today->modify('+14 days')];
}

function normalize_booking_date(?string $date): string
{
    [$min, $max] = date_window();
    $candidate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $date) ?: $min;
    if ($candidate < $min) {
        return $min->format('Y-m-d');
    }
    if ($candidate > $max) {
        return $max->format('Y-m-d');
    }
    return $candidate->format('Y-m-d');
}

function valid_booking_date(string $date): bool
{
    [$min, $max] = date_window();
    $candidate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if (!$candidate || $candidate->format('Y-m-d') !== $date) {
        return false;
    }
    return $candidate >= $min && $candidate <= $max && !is_weekend($date);
}

function next_booking_dates(): array
{
    [$min, $max] = date_window();
    $dates = [];
    for ($day = $min; $day <= $max; $day = $day->modify('+1 day')) {
        $dates[] = $day->format('Y-m-d');
    }
    return $dates;
}

function get_groomers(): array
{
    global $conn;
    $result = $conn->query('SELECT GRM_ID, GRM_GroomerName, GRM_HoursPerWeek FROM grm_groomers ORDER BY GRM_GroomerName');
    return $result->fetch_all(MYSQLI_ASSOC);
}

function groomer_by_id(string $groomerId): ?array
{
    global $conn;
    $stmt = $conn->prepare('SELECT GRM_ID, GRM_GroomerName, GRM_HoursPerWeek FROM grm_groomers WHERE GRM_ID = ? LIMIT 1');
    $stmt->bind_param('s', $groomerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function groomer_multiplier(?string $groomerId): float
{
    $groomer = $groomerId ? groomer_by_id($groomerId) : null;
    $name = $groomer['GRM_GroomerName'] ?? '';
    $multipliers = [
        'Sarah Mitchell' => 1.08,
        'Jake Thornton' => 1.00,
        'Priya Patel' => 1.06,
        'Cody Brennan' => 0.96,
        'Linda Kowalski' => 1.04,
    ];
    return $multipliers[$name] ?? 1.00;
}

function groomer_rate_note(array $groomer): string
{
    $multiplier = groomer_multiplier($groomer['GRM_ID']);
    $average = (int) round(68 * $multiplier);
    $maxBookings = (int) floor(((int) $groomer['GRM_HoursPerWeek'] * 60) / GROOMING_BOOKING_MINUTES);
    return $groomer['GRM_GroomerName'] . ' averages about $' . $average . ' per grooming visit and can take up to ' . $maxBookings . ' grooming bookings per week.';
}

function estimate_grooming_price(array $serviceKeys, string $dogSize, string $groomerId): float
{
    $services = grooming_services();
    $total = 0;
    foreach ($serviceKeys as $key) {
        if (isset($services[$key]['rates'][$dogSize])) {
            $total += $services[$key]['rates'][$dogSize];
        }
    }
    return round($total * groomer_multiplier($groomerId), 2);
}

function estimate_daycare_price(string $period, string $dogSize): float
{
    $rates = daycare_rates();
    return (float) ($rates[$period][$dogSize] ?? 0);
}

function current_user(): ?array
{
    global $conn;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM site_users WHERE USR_ID = ? LIMIT 1');
    $stmt->bind_param('s', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $user;
}

function require_login(string $returnUrl = 'account.php'): array
{
    $user = current_user();
    if (!$user) {
        redirect_to('login.php?return=' . urlencode($returnUrl) . '#login-form');
    }
    return $user;
}

function authenticate_user(string $email, string $password): bool
{
    global $conn;
    $stmt = $conn->prepare('SELECT * FROM site_users WHERE USR_Email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['USR_PasswordHash'])) {
        return false;
    }

    $_SESSION['user_id'] = $user['USR_ID'];
    return true;
}

function get_user_dogs(string $userId): array
{
    global $conn;
    $stmt = $conn->prepare('SELECT DOG_ID, DOG_Name, DOG_Size, DOG_Notes FROM site_dogs WHERE USR_ID = ? ORDER BY DOG_Name');
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $dogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $dogs;
}

function grooming_week_bounds(string $date): array
{
    $day = DateTimeImmutable::createFromFormat('Y-m-d', $date) ?: new DateTimeImmutable('today');
    $start = $day->modify('monday this week')->format('Y-m-d');
    $end = $day->modify('sunday this week')->format('Y-m-d');
    return [$start, $end];
}

function grooming_week_minutes_booked(string $date, string $groomerId): int
{
    global $conn;
    [$weekStart, $weekEnd] = grooming_week_bounds($date);
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM grmbk_groombookings
         WHERE GRM_ID = ?
           AND GRMBK_Date BETWEEN ? AND ?
           AND COALESCE(GRMBK_Status, 'pending') <> 'cancelled'"
    );
    $stmt->bind_param('sss', $groomerId, $weekStart, $weekEnd);
    $stmt->execute();
    $booked = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return $booked * GROOMING_BOOKING_MINUTES;
}

function groomer_week_has_capacity(string $date, string $groomerId): bool
{
    $groomer = groomer_by_id($groomerId);
    if (!$groomer) {
        return false;
    }
    $weeklyMinutes = (int) $groomer['GRM_HoursPerWeek'] * 60;
    return grooming_week_minutes_booked($date, $groomerId) + GROOMING_BOOKING_MINUTES <= $weeklyMinutes;
}

function grooming_slot_taken(string $date, string $time, string $groomerId): bool
{
    global $conn;
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM grmbk_groombookings
         WHERE GRMBK_Date = ?
           AND GRM_ID = ?
           AND GRMBK_StartTime = ?
           AND COALESCE(GRMBK_Status, 'pending') <> 'cancelled'"
    );
    $stmt->bind_param('sss', $date, $groomerId, $time);
    $stmt->execute();
    $total = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return $total > 0;
}

function grooming_slot_available(string $date, string $time, string $groomerId): bool
{
    if (!valid_booking_date($date)) {
        return false;
    }
    return !grooming_slot_taken($date, $time, $groomerId) && groomer_week_has_capacity($date, $groomerId);
}

function daycare_spots_left(string $date, string $period): int
{
    global $conn;
    if (!valid_booking_date($date)) {
        return 0;
    }

    if ($period === 'MORNING') {
        $periods = ['MORNING', 'FULL'];
    } elseif ($period === 'AFTERNOON') {
        $periods = ['AFTERNOON', 'FULL'];
    } else {
        $morning = daycare_spots_left($date, 'MORNING');
        $afternoon = daycare_spots_left($date, 'AFTERNOON');
        return min($morning, $afternoon);
    }

    $placeholders = implode(',', array_fill(0, count($periods), '?'));
    $types = 's' . str_repeat('s', count($periods));
    $sql = "SELECT COUNT(*) AS total
            FROM daybk_daycarebookings
            WHERE DAYBK_Date = ?
              AND DAYBK_StartPeriod IN ({$placeholders})
              AND COALESCE(DAYBK_Status, 'pending') <> 'cancelled'";
    $stmt = $conn->prepare($sql);
    $values = array_merge([$date], $periods);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $booked = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return max(0, DAYCARE_CAPACITY - $booked);
}

function booking_status_label(string $status): string
{
    return [
        'pending' => 'Pending confirmation',
        'approved' => 'Confirmed',
        'cancelled' => 'Cancelled',
    ][$status] ?? ucfirst($status);
}

function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function pretty_date(string $date): string
{
    $timestamp = strtotime($date);
    return $timestamp ? date('D, M j', $timestamp) : $date;
}

function image_path(string $relative): string
{
    return $relative;
}

function media_url(string $relativePath): string
{
    $segments = array_map('rawurlencode', explode('/', str_replace('\\', '/', $relativePath)));
    return implode('/', $segments);
}

function social_links(): array
{
    return [
        'Facebook' => 'https://facebook.com/fromwolftowow',
        'Instagram' => 'https://instagram.com/fromwolftowow',
        'X' => 'https://x.com/fromwolftowow',
    ];
}

function event_slug(string $title): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    return trim($slug, '-');
}

function get_events(): array
{
    $file = __DIR__ . '/content/Events.txt';
    if (!is_readable($file)) {
        return [];
    }

    $raw = trim(file_get_contents($file));
    $chunks = preg_split('/\R--/', "\n" . $raw);
    $events = [];

    foreach ($chunks as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '') {
            continue;
        }
        $lines = preg_split('/\R/', $chunk);
        $title = trim(array_shift($lines), "- \t\r\n");
        $body = trim(implode("\n", $lines));
        if ($title === '' || $body === '') {
            continue;
        }
        $paragraphs = preg_split('/\R{2,}/', $body);
        $events[] = [
            'title' => $title,
            'slug' => event_slug($title),
            'summary' => substr(trim($paragraphs[0]), 0, 210) . '...',
            'paragraphs' => $paragraphs,
        ];
    }

    return $events;
}

function get_event_by_slug(string $slug): ?array
{
    foreach (get_events() as $event) {
        if ($event['slug'] === $slug) {
            return $event;
        }
    }
    return null;
}
?>
