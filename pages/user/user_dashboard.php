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
    <title>User Dashboard - Zevent</title>
<link rel="stylesheet" href="../../assets/css/style.css">


    <style>
        body { padding-top:110px; }

        .status-pill {
            padding:6px 12px;
            border-radius:8px;
            font-weight:bold;
            color:white;
            font-size:13px;
        }
        .pending  { background:#f59e0b; }
        .verified { background:#10b981; }

        table td a.view-btn {
            padding:6px 14px;
            font-size:13px;
            display:inline-block;
            border-radius:8px;
            background: var(--accent);
            color:white;
            text-decoration:none;
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
        <input type="text" name="search" placeholder="Search events...">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">

        <!-- MY BOOKINGS -->
        <a href="user_dashboard.php" class="nav-btn nav-btn-primary">My Bookings</a>

        <!-- PROFILE DROPDOWN -->
        <div class="profile-menu">

    <!-- USER ICON (Don’t remove this!) -->
    <div class="profile-icon" onclick="toggleProfileMenu()">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true">
    </div>

    <!-- DROPDOWN MENU -->
    <div class="profile-dropdown" id="profileMenu">

        <div class="profile-header">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true">
            <div>
                <p class="profile-name"><?php echo htmlspecialchars($user_name); ?></p>
                <p class="profile-email">User Account</p>
            </div>
        </div>

        <a href="user_profile.php" class="profile-link">My Details</a>

        <a href="../logout.php" class="profile-link profile-link-danger">Logout</a>
    </div>
</div>



        <!-- HOST BUTTON -->
        <a href="pages/host/host_login.php" class="nav-btn nav-btn-host">Become a Host</a>

    </div>
</header>

<script>
function toggleProfileMenu() {
    document.getElementById("profileMenu").classList.toggle("show");
}
</script>

<!-- CONTENT -->
<div class="container">

    <h2 class="section-title">Welcome, <?php echo htmlspecialchars($user_name); ?></h2>

    <!-- BACK BUTTON -->
    <a href="../../index.php"
       class="nav-btn"
       style="display:inline-block; margin-bottom:15px; background:#0ea5e9; color:white; padding:8px 16px; border-radius:8px; text-decoration:none;">
       ← Back to Home
    </a>

    <h3 style="margin:20px 0;">Your Event Bookings</h3>

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

    echo '
    <table>
        <tr>
            <th>Event</th>
            <th>Date</th>
            <th>Ticket Code</th>
            <th>Transaction ID</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    ';

    while ($row = mysqli_fetch_assoc($res)) {

        $statusClass = ($row['ticket_status'] == "verified") ? "verified" : "pending";

        echo "
        <tr>
            <td>{$row['event_title']}</td>
            <td>{$row['event_date']}</td>
            <td>{$row['ticket_code']}</td>
            <td>{$row['transaction_id']}</td>

            <td>
                <span class='status-pill {$statusClass}'>
                    ".ucfirst($row['ticket_status'])."
                </span>
            </td>

            <td>
                <a href='../user/view_ticket.php?id={$row['reg_id']}' class='view-btn'>
                   View Ticket
                </a>
            </td>
        </tr>
        ";
    }

    echo "</table>";

} else {

    echo "<p>You have not registered for any events yet.</p>";
}
?>

</div>
<?php include "../../includes/footer.php"; ?>

</body>
</html>
