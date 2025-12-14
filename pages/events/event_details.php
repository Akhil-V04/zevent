<?php
session_start();
include "../../includes/db_connect.php";

$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['user_name'] : "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<h2>Invalid Event</h2>");
}

$event_id = intval($_GET['id']);

$sql = "
    SELECT e.*, h.name AS host_name 
    FROM events e
    JOIN hosts h ON e.host_id = h.host_id
    WHERE e.event_id = $event_id
";
$res = mysqli_query($connect, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("<h2>Event Not Found</h2>");
}

$event = mysqli_fetch_assoc($res);

$poster = (!empty($event['poster']))
    ? "../../assets/uploads/" . $event['poster']
    : "https://via.placeholder.com/500x600";

$cat = mysqli_real_escape_string($connect, $event['category']);
$sim = mysqli_query(
    $connect,
    "SELECT * FROM events WHERE category='$cat' AND event_id != $event_id LIMIT 4"
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['event_title']; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        body { padding-top:110px; }
        .details-box { display:flex; gap:40px; margin-top:20px; align-items:flex-start; }
        .details-left img { width:100%; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.4); }
        .details-right { font-size:18px; color:#e2e8f0; line-height:1.7; }
        .book-btn {
            margin-top:20px; padding:12px 25px; background:#4f46e5;
            color:white; border:none; border-radius:10px; cursor:pointer;
            font-size:18px; transition:0.2s;
        }
        .book-btn:hover { background:#4338ca; }
    </style>
</head>

<body>

<header class="nav-glass">
    <div class="nav-left"><div class="logo">⚡ Zevent</div></div>

    <form method="GET" action="../../index.php" class="search-box">
        <input type="text" name="search" placeholder="Search events...">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">
        <?php if ($user_logged_in): ?>
            <a href="../user/user_dashboard.php" class="nav-btn">My Bookings</a>
        <?php else: ?>
            <a href="../auth/login.php" class="nav-btn nav-btn-primary">Login</a>
        <?php endif; ?>
        <a href="../auth/login.php" class="nav-btn nav-btn-host">Become a Host</a>
    </div>
</header>

<div class="container">

<h2 class="section-title"><?php echo $event['event_title']; ?></h2>

<div class="details-box">

    <div class="details-left" style="width:40%;">
        <img src="<?php echo $poster; ?>">
    </div>

    <div class="details-right" style="width:60%;">

        <p><b>Hosted By:</b> <?php echo $event['host_name']; ?></p>
        <p><b>Date:</b> <?php echo $event['event_date']; ?></p>
        <p><b>Location:</b> <?php echo $event['city']; ?>, <?php echo $event['state']; ?></p>
        <p><b>Category:</b> <?php echo $event['category']; ?></p>

        <h3 style="margin-top:10px; font-size:30px; color:#facc15;">
            ₹<?php echo $event['price']; ?>
        </h3>

        <p style="margin-top:15px;">
            <?php echo nl2br($event['description']); ?>
        </p>

        <a href="payment.php?event_id=<?php echo $event_id; ?>">
            <button class="book-btn">Book Now</button>
        </a>

    </div>

</div>

<br><br>
<h3 class="section-title">Similar Events</h3>

<div class="event-grid">
<?php
if ($sim && mysqli_num_rows($sim) > 0) {
    while ($s = mysqli_fetch_assoc($sim)) {

        $simPoster = (!empty($s['poster']))
            ? "../../assets/uploads/" . $s['poster']
            : "https://via.placeholder.com/300x400";
?>
    <a href="event_details.php?id=<?php echo $s['event_id']; ?>" class="event-card">
        <img src="<?php echo $simPoster; ?>" class="event-img">
        <h3 class="event-title"><?php echo $s['event_title']; ?></h3>
        <p class="event-meta"><?php echo $s['category']; ?> • <?php echo $s['event_date']; ?></p>
    </a>
<?php
    }
} else {
    echo "<p>No similar events found.</p>";
}
?>
</div>

</div>
<?php include "../../includes/footer.php"; ?>

</body>
</html>
