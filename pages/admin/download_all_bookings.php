<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    die("Access denied.");
}

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=all_bookings.csv");

$output = fopen("php://output", "w");

// CSV headers
fputcsv($output, ["User", "Email", "Event", "Ticket Code", "Transaction ID", "Status", "Date"]);

$q = mysqli_query($conn,
    "SELECT r.*, 
            u.name AS user_name, u.email,
            e.event_title
     FROM registrations r
     JOIN users u ON r.user_id = u.user_id
     JOIN events e ON r.event_id = e.event_id
     ORDER BY r.reg_id DESC");

while ($row = mysqli_fetch_assoc($q)) {
    fputcsv($output, [
        $row['user_name'],
        $row['email'],
        $row['event_title'],
        $row['ticket_code'],
        $row['transaction_id'],
        $row['ticket_status'],
        $row['registered_at']
    ]);
}

fclose($output);
exit;
?>
