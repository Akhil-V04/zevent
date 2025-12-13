<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id = $_SESSION['host_id'];

// event_id check
if (!isset($_GET['event_id'])) {
    die("Invalid Event ID");
}

$event_id = intval($_GET['event_id']);

// validate event ownership
$eventSQL = "
    SELECT event_title, poster 
    FROM events 
    WHERE event_id = $event_id AND host_id = $host_id
";

$eventResult = mysqli_query($connect, $eventSQL);

if (!$eventResult || mysqli_num_rows($eventResult) == 0) {
    die("Access Denied.");
}

$event = mysqli_fetch_assoc($eventResult);

// fetch bookings
$bookingSQL = "
    SELECT 
        r.reg_id, 
        r.ticket_code, 
        r.transaction_id, 
        r.ticket_status,
        u.name AS user_name, 
        u.email, 
        u.phone
    FROM registrations r
    JOIN users u ON u.user_id = r.user_id
    WHERE r.event_id = $event_id
    ORDER BY r.reg_id DESC
";

$bookings = mysqli_query($connect, $bookingSQL);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Bookings - Zevent</title>
<link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body>

<!-- ========================= -->
<!--     HOST NAVBAR           -->
<!-- ========================= -->
<header class="nav-glass">
    <div class="nav-left">
        <div class="logo">⚡ Zevent Host</div>
    </div>

    <form method="GET" class="search-box" action="host_dashboard.php">
        <input type="text" name="search" placeholder="Search your events...">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">
        <a href="create_event.php" class="nav-btn nav-btn-primary">+ Create Event</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

    <h2 class="section-title">Bookings for: <?php echo htmlspecialchars($event['event_title']); ?></h2>

    <?php if (!empty($event['poster'])): ?>
        <img src="../../assets/uploads/<?php echo $event['poster']; ?>" 
             style="width:100px; border-radius:8px; margin-bottom:15px;">
    <?php endif; ?>

    <?php if (isset($_GET['verified'])): ?>
        <p style="color:green; font-weight:bold; margin:10px 0;">Ticket Verified Successfully.</p>
    <?php endif; ?>

    <p><a href="host_dashboard.php">← Back to Dashboard</a></p>

    <table>
        <tr>
            <th>User</th>
            <th>Contact</th>
            <th>Transaction ID</th>
            <th>Ticket Code</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($bookings)): ?>

                <?php 
                    $status_color = ($row['ticket_status'] == "verified") ? "green" : "orange";
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['email']); ?><br>
                        <?php echo htmlspecialchars($row['phone']); ?>
                    </td>

                    <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket_code']); ?></td>

                    <td style="color: <?php echo $status_color; ?>; font-weight:bold;">
                        <?php echo ucfirst($row['ticket_status']); ?>
                    </td>

                    <td>
                        <?php if ($row['ticket_status'] == "pending"): ?>

                            <a href="verify_ticket.php?reg_id=<?php echo $row['reg_id']; ?>&event_id=<?php echo $event_id; ?>"
                               style="color:white; background:green; padding:6px 10px; border-radius:5px; text-decoration:none;">
                                Verify
                            </a>

                        <?php else: ?>

                            <span style="color:green; font-weight:bold;">✔ Verified</span>

                        <?php endif; ?>
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr><td colspan="6">No bookings found.</td></tr>

        <?php endif; ?>
    </table>

</div>

</body>
</html>
