<?php
// File: C:\xampp\htdocs\pllcg82\auth\admin_logout.php

require_once '../config/config.php';

// Clear all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_logged_in']);

// Destroy the session
session_destroy();

// Redirect to admin login page
header('Location: admin_login.php');
exit;
?>