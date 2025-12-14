<?php
session_start();
include "../../includes/db_connect.php";

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .status-pill {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
            color: white;
            font-size: 13px;
            display: inline-block;
        }
        .pending  { background: #f59e0b; }
        .verified { background: #10b981; }

        table td a.view-btn {
            padding: 6px 14px;
            font-size: 13px;
            display: inline-block;
            border-radius: 8px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            white-space: nowrap;
        }
        
        table td a.view-btn:hover {
            background: var(--accent-hover);
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<header class="nav-glass">

    <div class="nav-left">
        <div class="logo">⚡ Zevent</div>
    </div>

    <form method="GET" action="../../index.php" class="search-box">
        <input type="text" name="search" placeholder="Search events, cities, categories" autocomplete="off">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">

        <!-- MY BOOKINGS -->
        <a href="user_dashboard.php" class="nav-btn nav-btn-primary">My Bookings</a>

        <!-- PROFILE DROPDOWN -->
        <div class="profile-menu">
            <div class="profile-icon" id="profileIconBtn">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true" alt="Profile">
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-header">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true" alt="Profile">
                    <div class="profile-info">
                        <p class="profile-name"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="profile-email">User Account</p>
                    </div>
                </div>

                <div class="profile-divider"></div>

                <a href="user_profile.php" class="profile-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Details
                </a>

                <div class="profile-divider"></div>

                <a href="../logout.php" class="profile-link profile-link-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <!-- HOST BUTTON -->
        <a href="../auth/login.php" class="nav-btn nav-btn-host">Become a Host</a>

    </div>
</header>

<script>
// Profile dropdown toggle
const profileIcon = document.getElementById('profileIconBtn');
const profileDropdown = document.getElementById('profileDropdown');

profileIcon.addEventListener('click', function(e) {
    e.stopPropagation();
    profileDropdown.classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
        profileDropdown.classList.remove('show');
    }
});

// Prevent dropdown from closing when clicking inside it
profileDropdown.addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>

<!-- CONTENT -->
<div class="container">

    <h2 class="welcome-text">Welcome, <?php echo htmlspecialchars($user_name); ?></h2>

    <!-- BACK BUTTON -->
    <a href="../../index.php" class="nav-btn" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Home
    </a>

    <h3 class="section-title" style="font-size: 24px; margin: 30px 0 20px 0;">Your Event Bookings</h3>

<?php
$sql = "
    SELECT r.*, e.event_title, e.event_date, e.price, e.poster
    FROM registrations r
    JOIN events e ON r.event_id = e.event_id
    WHERE r.user_id = $user_id
    ORDER BY r.reg_id DESC
";

$res = mysqli_query($connect, $sql);

if ($res && mysqli_num_rows($res) > 0) {

    echo '<div class="table-wrapper">';
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Event</th>';
    echo '<th>Date</th>';
    echo '<th>Ticket Code</th>';
    echo '<th>Transaction ID</th>';
    echo '<th>Status</th>';
    echo '<th>Action</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($row = mysqli_fetch_assoc($res)) {

        $statusClass = ($row['ticket_status'] == "verified") ? "verified" : "pending";
        $statusText = ucfirst($row['ticket_status']);

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['event_title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ticket_code']) . "</td>";
        echo "<td>" . htmlspecialchars($row['transaction_id']) . "</td>";
        echo "<td><span class='status-pill {$statusClass}'>{$statusText}</span></td>";
        echo "<td><a href='view_ticket.php?id={$row['reg_id']}' class='view-btn'>View Ticket</a></td>";
        echo "</tr>";
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

} else {

    echo '<div style="background: rgba(99, 102, 241, 0.1); padding: 30px; border-radius: 12px; text-align: center; margin-top: 20px;">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 15px; opacity: 0.5;">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />';
    echo '</svg>';
    echo '<p style="font-size: 18px; color: #94a3b8; margin-bottom: 20px;">You have not registered for any events yet.</p>';
    echo '<a href="../../index.php" class="btn-reset">Browse Events</a>';
    echo '</div>';
}
?>

</div>

<?php include "../../includes/footer.php"; ?>

</body>
</html>