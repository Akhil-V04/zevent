<?php
session_start();
include "../../includes/db_connect.php";

// Check host session
if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id   = $_SESSION['host_id'];
$host_name = $_SESSION['host_name'] ?? "Host";
$msg = "";

// DELETE EVENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event_id'])) {
    $delete_event_id = intval($_POST['delete_event_id']);

    $deleteSQL = "DELETE FROM events WHERE event_id = $delete_event_id AND host_id = $host_id";

    if (mysqli_query($connect, $deleteSQL)) {
        $msg = "Event deleted successfully.";
    } else {
        $msg = "Error deleting event: " . mysqli_error($connect);
    }
}

// STAT CARDS
$total_events = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total FROM events WHERE host_id = $host_id"
))['total'];

$total_bookings = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total 
     FROM registrations r 
     JOIN events e ON r.event_id = e.event_id 
     WHERE e.host_id = $host_id"
))['total'];

$pending_verifications = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total 
     FROM registrations r 
     JOIN events e ON r.event_id = e.event_id 
     WHERE e.host_id = $host_id AND r.ticket_status='pending'"
))['total'];

$verified_tickets = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total 
     FROM registrations r 
     JOIN events e ON r.event_id = e.event_id 
     WHERE e.host_id = $host_id AND r.ticket_status='verified'"
))['total'];

// EVENT LIST
// SEARCH FILTER
$search = "";
if (isset($_GET['search']) && $_GET['search'] !== "") {
    $search = mysqli_real_escape_string($connect, $_GET['search']);
}

// EVENT LIST (with search filter)
// SEARCH FILTER
$search = "";
$searchCondition = "";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = mysqli_real_escape_string($connect, trim($_GET['search']));
    $searchCondition = " AND e.event_title LIKE '%$search%' ";
}

// EVENT LIST (SHOW ALL unless searched)
$sql = "
    SELECT 
        e.event_id,
        e.event_title,
        e.category,
        e.poster,
        e.event_date,
        e.approval_status,
        (
            SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id
        ) AS total_bookings
    FROM events e
    WHERE e.host_id = $host_id
    $searchCondition
    ORDER BY e.event_id DESC
";

$events = mysqli_query($connect, $sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Host Dashboard - Zevent</title>
<link rel="stylesheet" href="../../assets/css/style.css">


<style>
/* Poster size fix */
.poster-small {
    width: 70px !important;
    height: 70px !important;
    border-radius: 8px !important;
    object-fit: cover !important;
}

/* Dashboard Stats */
.stats-container {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    flex: 1;
    background: #222;
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}
.stat-card h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
}
.stat-card p {
    font-size: 28px;
    font-weight: bold;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
    font-size: 12px;
    font-weight: bold;
}
.approved { background: green; }
.pending  { background: orange; }
.rejected { background: red; }

.action-btn {
    padding: 5px 8px;
    margin-right: 4px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-size: 13px;
}
.blue  { background: #0d6efd; }
.red   { background: #d90429; }
.green { background: green; }
</style>
</head>

<body>

<!-- NAVBAR -->
<header class="nav-glass">
    <div class="nav-left">
        <div class="logo">⚡ Zevent Host</div>
    </div>

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search your events...">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">
        <a href="create_event.php" class="nav-btn nav-btn-primary">+ Create Event</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

<h2 class="section-title">Welcome, <?php echo htmlspecialchars($host_name); ?></h2>

<?php if ($msg != ""): ?>
    <p style="color:blue; margin:10px 0;"><?php echo $msg; ?></p>
<?php endif; ?>

<?php if (isset($_GET['event_created'])): ?>
    <p style="color:green;">Event created successfully. Awaiting admin approval.</p>
<?php endif; ?>

<?php if (isset($_GET['event_updated'])): ?>
    <p style="color:green;">Event updated successfully. Awaiting re-approval.</p>
<?php endif; ?>

<!-- STATS -->
<div class="stats-container">
    <div class="stat-card"><h3>Total Events</h3><p><?php echo $total_events; ?></p></div>
    <div class="stat-card"><h3>Total Bookings</h3><p><?php echo $total_bookings; ?></p></div>
    <div class="stat-card"><h3>Pending Tickets</h3><p><?php echo $pending_verifications; ?></p></div>
    <div class="stat-card"><h3>Verified Tickets</h3><p><?php echo $verified_tickets; ?></p></div>
</div>

<!-- EVENTS TABLE -->
<h3>Your Events</h3>

<?php if ($events && mysqli_num_rows($events) > 0): ?>
<table>
    <tr>
        <th>Poster</th>
        <th>Title</th>
        <th>Category</th>
        <th>Date</th>
        <th>Status</th>
        <th>Bookings</th>
        <th>Actions</th>
    </tr>

<?php while ($ev = mysqli_fetch_assoc($events)):
    $poster = (!empty($ev['poster']))
        ? "../../assets/uploads/" . $ev['poster']
        : "https://via.placeholder.com/100x140";

    $badge = $ev['approval_status'];
?>
<tr>
    <td><img src="<?php echo $poster; ?>" class="poster-small"></td>
    <td><?php echo $ev['event_title']; ?></td>
    <td><?php echo $ev['category']; ?></td>
    <td><?php echo $ev['event_date']; ?></td>

    <td><span class="status-badge <?php echo $badge; ?>"><?php echo ucfirst($badge); ?></span></td>

    <td><?php echo $ev['total_bookings']; ?></td>

    <td>
        <a href="host_view_bookings.php?event_id=<?php echo $ev['event_id']; ?>" class="action-btn blue">Bookings</a>
        <a href="edit_event.php?event_id=<?php echo $ev['event_id']; ?>" class="action-btn green">Edit</a>

        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
            <input type="hidden" name="delete_event_id" value="<?php echo $ev['event_id']; ?>">
            <button type="submit" class="action-btn red" style="border:none; cursor:pointer;">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>

<?php else: ?>
    <p>You have not created any events yet.</p>
<?php endif; ?>

</div>

</body>
</html>
