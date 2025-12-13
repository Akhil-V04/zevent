<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// COUNT CARDS
$users      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$hosts      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM hosts"))[0];
$events     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM events"))[0];
$pending    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM events WHERE approval_status='pending'"))[0];
$bookings   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM registrations"))[0];

// BOOKINGS PER CATEGORY
$catData = mysqli_query($conn,
    "SELECT category, COUNT(*) AS total 
     FROM events e 
     JOIN registrations r ON e.event_id = r.event_id
     GROUP BY category");

$catLabels = [];
$catCounts = [];

while ($row = mysqli_fetch_assoc($catData)) {
    $catLabels[] = $row['category'];
    $catCounts[] = $row['total'];
}

// BOOKINGS PER EVENT (Top 5)
$eventData = mysqli_query($conn,
    "SELECT event_title, COUNT(*) AS total
     FROM events e
     JOIN registrations r ON e.event_id = r.event_id
     GROUP BY event_title
     ORDER BY total DESC
     LIMIT 5");

$eventLabels = [];
$eventCounts = [];

while ($row = mysqli_fetch_assoc($eventData)) {
    $eventLabels[] = $row['event_title'];
    $eventCounts[] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Zevent</title>
<link rel="stylesheet" href="../../assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<!-- NAVBAR -->
<header class="nav-glass">
    <div class="nav-left">
        <div class="logo">⚡ Zevent Admin</div>
    </div>

    <div class="nav-right">
        <a href="admin_dashboard.php" class="nav-btn nav-btn-primary">Dashboard</a>

        <a href="admin_approve_events.php" class="nav-btn">Approvals</a>

        <a href="admin_manage_users.php" class="nav-btn">Users</a>

        <a href="admin_manage_hosts.php" class="nav-btn">Hosts</a>
        <!-- ⭐ NEW BUTTON ADDED HERE -->
        <a href="admin_booking_categories.php" class="nav-btn">Bookings</a>

        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>
<div class="admin-page">
<div class="container">

    <h2 class="section-title">Admin Dashboard</h2>

    <!-- STAT CARDS -->
    <div class="card-container">

        <div class="card">
            <h2><?php echo $users; ?></h2>
            <p>Total Users</p>
        </div>

        <div class="card">
            <h2><?php echo $hosts; ?></h2>
            <p>Total Hosts</p>
        </div>

        <div class="card">
            <h2><?php echo $events; ?></h2>
            <p>Total Events</p>
        </div>

        <div class="card">
            <h2><?php echo $pending; ?></h2>
            <p>Pending Approvals</p>
        </div>

        <div class="card">
            <h2><?php echo $bookings; ?></h2>
            <p>Total Bookings</p>
        </div>

    </div>

    <br><br>

    <a class="btn-reset" href="download_all_bookings.php">
        ⬇ Download All Bookings (CSV)
    </a>

    <br><br>
    <h2 class="section-title">Analytics</h2>

    <!-- CHART 1 -->
    <div style="max-width: 600px; margin-bottom: 40px;">
        <canvas id="categoryChart"></canvas>
    </div>

    <!-- CHART 2 -->
    <div style="max-width: 600px;">
        <canvas id="eventChart"></canvas>
    </div>

</div>

<script>
// PIE CHART
new Chart(document.getElementById('categoryChart'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($catCounts); ?>,
            backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444','#818cf8']
        }]
    }
});

// BAR CHART
new Chart(document.getElementById('eventChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($eventLabels); ?>,
        datasets: [{
            label: 'Bookings',
            data: <?php echo json_encode($eventCounts); ?>,
            backgroundColor: '#6366f1'
        }]
    },
    options: {
        scales: { y: { beginAtZero: true, ticks:{ color:'#f1f5f9' }, grid:{color:'#333'} } },
        plugins:{ legend:{ labels:{ color:'#f1f5f9' } } }
    }
});
</script>

</body>
</html>
