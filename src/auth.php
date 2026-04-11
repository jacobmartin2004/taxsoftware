<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login.php from any subfolder
    $dir = dirname($_SERVER['SCRIPT_NAME']);
    $login = (strpos($dir, '/public') !== false || strpos($dir, '/src') !== false)
        ? '../login.php' : 'login.php';
    header('Location: ' . $login);
    exit;
}
