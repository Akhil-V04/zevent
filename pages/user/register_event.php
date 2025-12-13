<?php
session_start();
include "../../includes/db_connect.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

if (!isset($_POST['event_id']) || !isset($_POST['transaction_id'])) {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];
$event_id = intval($_POST['event_id']);
$txn = $_POST['transaction_id'];

// generate ticket code
$ticket_code = "ZE-" . strtoupper(substr(md5(uniqid()), 0, 8));

$q = "INSERT INTO registrations (user_id, event_id, ticket_code, transaction_id, payment_status, ticket_status)
      VALUES ('$user_id', '$event_id', '$ticket_code', '$txn', 'paid', 'pending')";
if (mysqli_query($connect, $q)) {
    header("Location: user_dashboard.php?success=1");
    exit;
} else {
    echo "Error: " . mysqli_error($connect);
}
?>
