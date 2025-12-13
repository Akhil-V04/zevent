<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch categories
$categories = mysqli_query($connect, "SELECT DISTINCT category FROM events ORDER BY category ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Bookings by Category</title>
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

    <h2 class="section-title">Bookings — Select Category</h2>

    <!-- CATEGORY GRID -->
    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));
        gap:20px;
    ">

        <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
        
        <a href="admin_booking_events.php?category=<?php echo urlencode($c['category']); ?>" 
           style="
               display:block;
               padding:20px;
               background:#1a1a2e;
               color:white;
               border:1px solid rgba(99,102,241,0.3);
               border-radius:12px;
               text-decoration:none;
               font-size:18px;
               font-weight:600;
               transition:0.3s;
               box-shadow:var(--shadow-sm);
           "
           onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)'"
        >
            <?php echo $c['category']; ?> →
        </a>

        <?php } ?>

    </div>

</div>

</body>
</html>
