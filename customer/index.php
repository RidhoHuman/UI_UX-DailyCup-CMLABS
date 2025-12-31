<?php
$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Redirect to main customer page (menu)
header('Location: ' . SITE_URL . '/customer/menu.php');
exit;
