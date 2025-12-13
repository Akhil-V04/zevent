<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$msg = "";

/* ---------------------------------------------------------
   HARD DELETE HOST (CASCADE WILL DELETE EVENTS + BOOKINGS)
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_host_id'])) {

    $host_id = intval($_POST['delete_host_id']);

    // Direct delete, cascade will handle child rows
    $deleteSQL = "DELETE FROM hosts WHERE host_id = $host_id";

    if (mysqli_query($conn, $deleteSQL)) {
        $msg = "✔ Host deleted successfully (all related events & bookings removed).";
    } else {
        $msg = "❌ Error deleting host: " . mysqli_error($conn);
    }
}

/* ---------------------------------------------------------
   FETCH HOST LIST + EVENT COUNT
---------------------------------------------------------- */
$sql = "
    SELECT 
        h.host_id,
        h.name,
        h.email,
        h.phone,
        h.upi_id,
        (SELECT COUNT(*) FROM events e WHERE e.host_id = h.host_id) AS total_events
    FROM hosts h
    ORDER BY h.host_id DESC
";

$hosts = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Hosts - Admin Panel</title>
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
        <a href="admin_approve_events.php" class="nav-btn">Approvals</a>
        <a href="admin_manage_users.php" class="nav-btn">Users</a>
        <a href="admin_manage_hosts.php" class="nav-btn nav-btn-primary">Hosts</a>
        <a href="admin_booking_categories.php" class="nav-btn">Bookings</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>
<div class="admin-page">
<div class="container">

<h2 class="section-title">Manage Hosts</h2>

<?php if ($msg !== ""): ?>
    <p style="color:#4ade80; font-weight:600; margin-bottom:15px;">
        <?php echo $msg; ?>
    </p>
<?php endif; ?>

<table>
    <tr>
        <th>ID</th>
        <th>Host Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>UPI ID</th>
        <th>Total Events</th>
        <th>Action</th>
    </tr>

<?php if ($hosts && mysqli_num_rows($hosts) > 0): ?>

    <?php while ($row = mysqli_fetch_assoc($hosts)): ?>
        <tr>
            <td><?php echo $row['host_id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['upi_id']; ?></td>
            <td><?php echo $row['total_events']; ?></td>

            <td>
                <form method="POST" 
                    style="display:inline;"
                    onsubmit="return confirm('⚠ WARNING: This will delete the host AND all their events AND all users’ bookings for these events. Continue?');">

                    <input type="hidden" name="delete_host_id" value="<?php echo $row['host_id']; ?>">

                    <button type="submit"
                        style="background:none; border:none; color:var(--danger); font-weight:bold; cursor:pointer;">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>

<?php else: ?>

    <tr>
        <td colspan="7" style="text-align:center; padding:20px;">No hosts found.</td>
    </tr>

<?php endif; ?>

</table>

</div>

</body>
</html>
