<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Redirect to unified authentication portal with register tab active
header('Location: login.php?tab=register');
exit;
