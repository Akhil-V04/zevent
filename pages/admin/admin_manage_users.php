<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$msg = "";

/* ---------------------------------------------------------
   FORCE DELETE USER (CASCADE WILL DELETE REGISTRATIONS)
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {

    $user_id = intval($_POST['delete_user_id']);

    $deleteSQL = "DELETE FROM users WHERE user_id = $user_id";

    if (mysqli_query($conn, $deleteSQL)) {
        $msg = "✔ User deleted successfully.";
    } else {
        $msg = "❌ Error deleting user: " . mysqli_error($conn);
    }
}

/* ---------------------------------------------------------
   FETCH ALL USERS + COUNT OF THEIR BOOKINGS
---------------------------------------------------------- */
$sql = "
    SELECT 
        u.user_id, 
        u.name, 
        u.email, 
        u.phone,
        (SELECT COUNT(*) FROM registrations r WHERE r.user_id = u.user_id) AS total_bookings
    FROM users u
    ORDER BY u.user_id DESC
";

$users = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin Panel</title>
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
        <a href="admin_manage_users.php" class="nav-btn nav-btn-primary">Users</a>
        <a href="admin_manage_hosts.php" class="nav-btn">Hosts</a>
        <a href="admin_booking_categories.php" class="nav-btn">Bookings</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

<h2 class="section-title">Manage Users</h2>

<?php if ($msg !== ""): ?>
    <p style="color:#4ade80; font-weight:600; margin-bottom:15px;">
        <?php echo $msg; ?>
    </p>
<?php endif; ?>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Total Bookings</th>
        <th>Action</th>
    </tr>

<?php if ($users && mysqli_num_rows($users) > 0): ?>

    <?php while ($row = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['total_bookings']; ?></td>

            <td>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('⚠ WARNING: This will delete the user AND all their bookings. Continue?');">

                    <input type="hidden" name="delete_user_id" value="<?php echo $row['user_id']; ?>">

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
        <td colspan="6" style="text-align:center; padding:20px;">No users found.</td>
    </tr>
<?php endif; ?>

</table>

</div>

</body>
</html>
