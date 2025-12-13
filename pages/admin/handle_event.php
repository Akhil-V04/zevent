<?php
session_start();
include "../../includes/db_connect.php";


if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);
$action = $_GET['action'];

if ($action == "approve") {
    $sql = "UPDATE events SET approval_status='approved' WHERE event_id=$id";
} elseif ($action == "reject") {
    $sql = "UPDATE events SET approval_status='rejected' WHERE event_id=$id";
} else {
    die("Invalid action.");
}

mysqli_query($conn, $sql);

header("Location: admin_approve_events.php");
exit;
?>
