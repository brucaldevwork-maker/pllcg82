<?php
// File: C:\xampp\htdocs\pllcg82\config\config.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$dbname = 'pllc_enterprise'; // Change this to your actual database name
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Site configuration
define('SITE_URL', 'http://localhost/pllcg82');
define('ADMIN_URL', SITE_URL . '/admin');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>