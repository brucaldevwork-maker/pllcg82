<?php
// File: C:\xampp\htdocs\pllcg82\auth\logout.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, delete it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear any remember me cookie if you have one
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Clear any cart session variables if needed
if (isset($_SESSION['session_id'])) {
    unset($_SESSION['session_id']);
}

// Set logout success message
session_start(); // Start a new session for the message
$_SESSION['logout_message'] = "You have been successfully logged out.";
$_SESSION['message_type'] = "success";

// Redirect to login page with success message
header("Location: login.php");
exit();
?>