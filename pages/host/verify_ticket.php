<?php
session_start();
include "../../includes/db_connect.php";

// Host authentication
if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id = $_SESSION['host_id'];

// Validate input
if (!isset($_GET['reg_id']) || !isset($_GET['event_id'])) {
    die("Invalid Request.");
}

$reg_id  = intval($_GET['reg_id']);
$event_id = intval($_GET['event_id']);

// --------------------------------------------------
// Verify ticket only if this booking belongs to host
// --------------------------------------------------
$verifySQL = "
    UPDATE registrations r
    JOIN events e ON r.event_id = e.event_id
    SET r.ticket_status = 'verified'
    WHERE r.reg_id = $reg_id
    AND r.event_id = $event_id
    AND e.host_id = $host_id
";

mysqli_query($connect, $verifySQL);

// Redirect back to event bookings page
header("Location: host_view_bookings.php?event_id=$event_id&verified=1");
exit;
?>
