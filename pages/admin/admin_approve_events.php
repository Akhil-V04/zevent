<?php
session_start();
include "../../includes/db_connect.php";

// Admin check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$msg = "";

// HANDLE APPROVE / REJECT (POST ONLY)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['approve_event_id'])) {
        $event_id = intval($_POST['approve_event_id']);
        if (mysqli_query($conn, "UPDATE events SET approval_status='approved' WHERE event_id=$event_id")) {
            $msg = "Event approved successfully.";
        }
    }

    if (isset($_POST['reject_event_id'])) {
        $event_id = intval($_POST['reject_event_id']);
        if (mysqli_query($conn, "UPDATE events SET approval_status='rejected' WHERE event_id=$event_id")) {
            $msg = "Event rejected successfully.";
        }
    }
}

// FILTER STATUS
$statusFilter = $_GET['status'] ?? "pending";
$validStatuses = ["pending", "approved", "rejected", "all"];
if (!in_array($statusFilter, $validStatuses)) {
    $statusFilter = "pending";
}

// WHERE CLAUSE
$where = "1=1";
if ($statusFilter !== "all") {
    $where .= " AND e.approval_status='$statusFilter'";
}

// FETCH EVENTS
$sql = "
    SELECT 
        e.event_id,
        e.event_title,
        e.category,
        e.city,
        e.state,
        e.event_date,
        e.approval_status,
        h.name AS host_name,
        h.email AS host_email
    FROM events e
    JOIN hosts h ON e.host_id = h.host_id
    WHERE $where
    ORDER BY e.event_id DESC
";
$events = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approve Events - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<!-- NAVBAR -->
<header class="nav-glass">
    <div class="nav-left">
        <div class="logo">⚡ Zevent Admin</div>
    </div>

    <div class="nav-right">
        <a href="admin_dashboard.php" class="nav-btn">Dashboard</a>
        <a href="admin_approve_events.php" class="nav-btn nav-btn-primary">Approvals</a>
        <a href="admin_manage_users.php" class="nav-btn">Users</a>
        <a href="admin_manage_hosts.php" class="nav-btn">Hosts</a>
        <a href="admin_booking_categories.php" class="nav-btn">Bookings</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

<h2 class="section-title">Event Approvals</h2>

<?php if ($msg != ""): ?>
    <p style="color:var(--success); font-weight:600;"><?php echo $msg; ?></p>
<?php endif; ?>

<!-- FILTER BUTTONS -->
<div style="margin-bottom:25px; display:flex; gap:12px; flex-wrap:wrap;">

    <a href="admin_approve_events.php?status=pending"
       class="chip <?php echo ($statusFilter=='pending')?'chip-active':''; ?>">
       Pending
    </a>

    <a href="admin_approve_events.php?status=approved"
       class="chip <?php echo ($statusFilter=='approved')?'chip-active':''; ?>">
       Approved
    </a>

    <a href="admin_approve_events.php?status=rejected"
       class="chip <?php echo ($statusFilter=='rejected')?'chip-active':''; ?>">
       Rejected
    </a>

    <a href="admin_approve_events.php?status=all"
       class="chip <?php echo ($statusFilter=='all')?'chip-active':''; ?>">
       All
    </a>

</div>

<!-- EVENTS TABLE -->
<table>
    <tr>
        <th>ID</th>
        <th>Event</th>
        <th>Category</th>
        <th>Date</th>
        <th>Location</th>
        <th>Host</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

<?php
if ($events && mysqli_num_rows($events) > 0) {
    while ($ev = mysqli_fetch_assoc($events)) {

        $color = "var(--warning)";
        if ($ev['approval_status'] == "approved") $color = "var(--success)";
        if ($ev['approval_status'] == "rejected") $color = "var(--danger)";
?>
<tr>
    <td><?php echo $ev['event_id']; ?></td>
    <td><?php echo $ev['event_title']; ?></td>
    <td><?php echo $ev['category']; ?></td>
    <td><?php echo $ev['event_date']; ?></td>
    <td><?php echo $ev['city']; ?>, <?php echo $ev['state']; ?></td>
    <td><?php echo $ev['host_name']; ?><br><small><?php echo $ev['host_email']; ?></small></td>
    <td style="color:<?php echo $color; ?>; font-weight:bold;">
        <?php echo ucfirst($ev['approval_status']); ?>
    </td>
    <td>

        <?php if ($ev['approval_status'] == "pending"): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="approve_event_id" value="<?php echo $ev['event_id']; ?>">
                <button type="submit" class="btn-success">Approve</button>
            </form>

            <form method="post" style="display:inline;">
                <input type="hidden" name="reject_event_id" value="<?php echo $ev['event_id']; ?>">
                <button type="submit" class="btn-danger">Reject</button>
            </form>

        <?php elseif ($ev['approval_status'] == "approved"): ?>
            <span>Approved</span>
            <form method="post" style="display:inline;">
                <input type="hidden" name="reject_event_id" value="<?php echo $ev['event_id']; ?>">
                <button type="submit" class="btn-danger">Reject</button>
            </form>

        <?php else: ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="approve_event_id" value="<?php echo $ev['event_id']; ?>">
                <button type="submit" class="btn-success">Approve</button>
            </form>
            <span>Rejected</span>
        <?php endif; ?>

    </td>
</tr>
<?php
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center; padding:20px;'>No events found.</td></tr>";
}
?>

</table>

</div>

</body>
</html>
