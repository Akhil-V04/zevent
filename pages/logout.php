<?php
session_start();

// If host logs out → send to host login
if (isset($_SESSION['host_id'])) {
    session_unset();
    session_destroy();
    header("Location: host/host_login.php");
    exit;
}

// If user logs out → send to user login
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    header("Location: user/user_login.php");
    exit;
}

// If admin logs out → send to admin login
if (isset($_SESSION['admin_id'])) {
    session_unset();
    session_destroy();
    header("Location: admin/admin_login.php");
    exit;
}

// Default fallback
session_unset();
session_destroy();
header("Location: index.php");
exit;
?>
