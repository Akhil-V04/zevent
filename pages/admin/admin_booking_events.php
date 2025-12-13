session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['category'])) {
    die("Invalid category");
}

$category = mysqli_real_escape_string($conn, $_GET['category']);

$events = mysqli_query($conn,
    "SELECT event_id, event_title, event_date
     FROM events
     WHERE category='$category'
     ORDER BY event_date DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Events in <?php echo $category; ?></title>
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

<div class="container">

    <h2 class="section-title">Events — <?php echo $category; ?></h2>

    <!-- BACK BUTTON -->
    <a href="admin_booking_categories.php" class="btn-reset" style="margin-bottom:25px; display:inline-block;">
        ← Back to Categories
    </a>

    <!-- EVENTS GRID -->
    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));
        gap:20px;
    ">

        <?php 
        if ($events && mysqli_num_rows($events) > 0) {
            while ($e = mysqli_fetch_assoc($events)) { ?>

                <a href="admin_booking_list.php?event=<?php echo $e['event_id']; ?>&category=<?php echo urlencode($category); ?>"
                   style="
                       display:block;
                       padding:20px;
                       background:#1a1a2e;
                       border-radius:12px;
                       border:1px solid rgba(99,102,241,0.2);
                       text-decoration:none;
                       color:#f1f5f9;
                       transition:0.3s ease;
                       box-shadow:var(--shadow-sm);
                   "
                   onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='var(--shadow-lg)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)'"
                >
                    <h3 style="font-size:20px; margin-bottom:8px; color:#6366f1;">
                        <?php echo $e['event_title']; ?>
                    </h3>

                    <p style="color:#94a3b8; font-size:14px;">
                        📅 <?php echo $e['event_date']; ?>
                    </p>
                </a>

        <?php 
            }
        } else { ?>

            <p style="color:#94a3b8;">No events found for this category.</p>

        <?php } ?>

    </div>

</div>

</body>
</html>
