<?php
require_once __DIR__ . '/app.php';
session_destroy();
session_start();
set_flash('success', 'You have been logged out.');
redirect_to('index.php');
?>
