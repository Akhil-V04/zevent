<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$filter_category = $_GET['category'] ?? "all";
$filter_event    = $_GET['event'] ?? "all";
$filter_status   = $_GET['status'] ?? "all";
$search          = $_GET['search'] ?? "";

// ------------------------------------------------------
// BUILD WHERE QUERY
// ------------------------------------------------------
$where = "1=1";

if ($filter_category !== "all") {
    $where .= " AND e.category = '$filter_category'";
}

if ($filter_event !== "all") {
    $where .= " AND r.event_id = $filter_event";
}

if ($filter_status !== "all") {
    $where .= " AND r.ticket_status = '$filter_status'";
}

if ($search !== "") {
    $search = mysqli_real_escape_string($conn, $search);
    $where .= "
        AND (
            u.name LIKE '%$search%' OR
            u.email LIKE '%$search%' OR
            u.phone LIKE '%$search%' OR
            e.event_title LIKE '%$search%' OR
            e.category LIKE '%$search%' OR
            h.name LIKE '%$search%' OR
            r.transaction_id LIKE '%$search%' OR
            r.ticket_code LIKE '%$search%'
        )
    ";
}

// ------------------------------------------------------
// FETCH BOOKINGS
// ------------------------------------------------------
$sql = "
SELECT 
    r.reg_id,
    r.transaction_id,
    r.ticket_code,
    r.ticket_status,
    r.registered_at,

    u.name AS user_name,
    u.email AS user_email,
    u.phone AS user_phone,

    e.event_title,
    e.category,
    e.event_date AS event_date,

    h.name AS host_name
FROM registrations r
JOIN users u ON u.user_id = r.user_id
JOIN events e ON e.event_id = r.event_id
JOIN hosts h ON h.host_id = e.host_id
WHERE $where
ORDER BY r.reg_id DESC
";

$bookings = mysqli_query($conn, $sql);

// Fetch dropdown options
$events = mysqli_query($conn, "SELECT event_id, event_title FROM events ORDER BY event_title ASC");
$dynamicCats = mysqli_query($conn, "SELECT DISTINCT category FROM events ORDER BY category ASC");
$staticCategories = ["Concert", "Workshops", "College Fests", "Others"];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bookings - Admin - Zevent</title>
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
        <a href="admin_manage_hosts.php" class="nav-btn">Hosts</a>
        <a href="admin_booking_categories.php" class="nav-btn nav-btn-primary">Bookings</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>
<div class="admin-page">
<div class="container">

    <h2 class="section-title">All Bookings</h2>

    <!-- BACK BUTTON -->
    <a href="admin_booking_categories.php" class="btn-reset" style="margin-bottom:20px; display:inline-block;">
        ← Back to Categories
    </a>

    <!-- FILTER BAR -->
    <form method="GET" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:25px;">

        <input type="text" name="search" 
               placeholder="Search user, event, host, Txn ID..." 
               value="<?php echo $search; ?>">

        <!-- CATEGORY FILTER -->
        <select name="category">
            <option value="all">All Categories</option>

            <?php foreach ($staticCategories as $cat): ?>
                <option value="<?php echo $cat; ?>" 
                    <?php if ($filter_category == $cat) echo "selected"; ?>>
                    <?php echo $cat; ?>
                </option>
            <?php endforeach; ?>

            <?php while ($c = mysqli_fetch_assoc($dynamicCats)): ?>
                <?php if (!in_array($c['category'], $staticCategories)): ?>
                    <option value="<?php echo $c['category']; ?>"
                        <?php if ($filter_category == $c['category']) echo "selected"; ?>>
                        <?php echo $c['category']; ?>
                    </option>
                <?php endif; ?>
            <?php endwhile; ?>
        </select>

        <!-- EVENT FILTER -->
        <select name="event">
            <option value="all">All Events</option>
            <?php while ($ev = mysqli_fetch_assoc($events)): ?>
                <option value="<?php echo $ev['event_id']; ?>"
                    <?php if ($filter_event == $ev['event_id']) echo "selected"; ?>>
                    <?php echo $ev['event_title']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- STATUS FILTER -->
        <select name="status">
            <option value="all">All Status</option>
            <option value="pending"  <?php if ($filter_status=="pending") echo "selected"; ?>>Pending</option>
            <option value="verified" <?php if ($filter_status=="verified") echo "selected"; ?>>Verified</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <!-- BOOKINGS TABLE -->
    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Event</th>
            <th>Category</th>
            <th>Host</th>
            <th>Txn ID</th>
            <th>Ticket Code</th>
            <th>Status</th>
            <th>Date</th>
        </tr>

        <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                <?php $color = $row['ticket_status']=="verified" ? "var(--success)" : "var(--warning)"; ?>

                <tr>
                    <td><?php echo $row['reg_id']; ?></td>
                    <td><?php echo $row['user_name']; ?></td>
                    <td><?php echo $row['user_email']; ?></td>
                    <td><?php echo $row['user_phone']; ?></td>
                    <td><?php echo $row['event_title']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['host_name']; ?></td>
                    <td><?php echo $row['transaction_id']; ?></td>
                    <td><?php echo $row['ticket_code']; ?></td>
                    <td style="color:<?php echo $color; ?>; font-weight:bold;">
                        <?php echo ucfirst($row['ticket_status']); ?>
                    </td>
                    <td><?php echo $row['registered_at']; ?></td>
                </tr>
            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="11" style="text-align:center; padding:20px; color:#94a3b8;">
                    No bookings found.
                </td>
            </tr>

        <?php endif; ?>

    </table>

</div>

</body>
</html>
