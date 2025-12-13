<?php
session_start();
include "../../includes/db_connect.php";
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch ALL bookings grouped by category, then event
$q = mysqli_query($conn,
    "SELECT r.*, 
            u.name AS user_name, u.email AS user_email,
            e.event_title, e.category, e.event_date AS event_date,
            h.name AS host_name
     FROM registrations r
     JOIN users u ON r.user_id = u.user_id
     JOIN events e ON r.event_id = e.event_id
     JOIN hosts h ON e.host_id = h.host_id
     ORDER BY e.category, e.event_title, r.reg_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>All Bookings (Grouped) - Admin</title>
<link rel="stylesheet" href="../../assets/css/style.css">


<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }

    .category-box {
        background: #0077ff;
        color: white;
        padding: 10px;
        font-size: 20px;
        margin-top: 25px;
        border-radius: 6px;
    }

    .event-box {
        background: white;
        padding: 15px;
        border-radius: 6px;
        margin-top: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .event-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
        color: #222;
    }

    .booking-item {
        background: #f8f8f8;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid #ddd;
    }

    .pending { color: orange; font-weight: bold; }
    .verified { color: green; font-weight: bold; }

</style>
</head>
<body>

<h2>All Bookings (Organized View)</h2>

<?php
$current_category = "";
$current_event = "";

if (mysqli_num_rows($q) > 0) {
    while ($b = mysqli_fetch_assoc($q)) {

        // New Category Section
        if ($current_category != $b['category']) {
            $current_category = $b['category'];
            echo "<div class='category-box'>{$current_category}</div>";
            $current_event = ""; // reset event grouping
        }

        // New Event Section
        if ($current_event != $b['event_title']) {
            $current_event = $b['event_title'];
            echo "<div class='event-box'>
                    <div class='event-title'>{$current_event} 
                    <span style='color:#555; font-size:14px;'>({$b['event_date']})</span>
                    </div>";
        }

        // Booking item inside event
        $statusClass = ($b['ticket_status'] == 'verified') ? "verified" : "pending";

        echo "
            <div class='booking-item'>
                <strong>User:</strong> {$b['user_name']} ({$b['user_email']})<br>
                <strong>Ticket Code:</strong> {$b['ticket_code']}<br>
                <strong>Transaction ID:</strong> {$b['transaction_id']}<br>
                <strong>Status:</strong> <span class='{$statusClass}'>{$b['ticket_status']}</span><br>
                <strong>Booked At:</strong> {$b['registered_at']}<br>
            </div>
        ";

        // Close event-box only when next event changes
        $next = mysqli_fetch_assoc($q);
        if (!$next || $next['event_title'] != $current_event) {
            echo "</div>"; // close event-box
        }
        if ($next) mysqli_data_seek($q, mysqli_num_rows($q) - mysqli_num_rows($q)); // continue loop
    }

} else {
    echo "<p>No bookings found.</p>";
}
?>

<br>
<a href="admin_dashboard.php">Back</a>

</body>
</html>
