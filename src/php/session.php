<?php
require 'api.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$currentFile = basename($_SERVER['PHP_SELF']);

// Handle logic based on the current page
if ($currentFile == 'watch.php') {
    // Redirect to index.php if no session or session validation fails
    if (!isset($_SESSION['active']) || $_SESSION['active'] !== true || !isset($_SESSION['token'])) {
        header('Location: /');
        exit;
    } else {
        $valid = postData('session', ['session' => $_SESSION['token']]);
        if ($valid['status'] != 200) {
            header('Location: /');
            exit;
        }
    }
} elseif ($currentFile == 'index.php') {
    // Redirect to watch.php if session exists and session validation is successful
    if (isset($_SESSION['active']) && $_SESSION['active'] === true && isset($_SESSION['token'])) {
        $valid = postData('session', ['session' => $_SESSION['token']]);
        if ($valid['status'] == 200) {
            header('Location: /watch.php');
            exit;
        }
    }
    // Do nothing if no session or session validation fails
}

// Debug to log any unhandled conditions
error_log("No redirection occurred for file: $currentFile with session active status: " . ($_SESSION['active'] ?? 'undefined'));
?>
