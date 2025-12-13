<?php
session_start();
include "../../includes/db_connect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "";

// Validate ticket
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ticket.");
}

$reg_id = intval($_GET['id']);

// Fetch ticket data
$sql = "
SELECT r.*, 
       e.event_title, e.poster, e.event_date, e.city, e.state, e.price, 
       h.name AS host_name,
       u.name AS uname, u.email AS uemail, u.phone AS uphone
FROM registrations r
JOIN events e ON r.event_id = e.event_id
JOIN hosts h  ON e.host_id = h.host_id
JOIN users u  ON r.user_id = u.user_id
WHERE r.reg_id = $reg_id AND r.user_id = $user_id
LIMIT 1
";
$res = mysqli_query($connect, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("Ticket not found or access denied.");
}

$t = mysqli_fetch_assoc($res);

// Poster
$poster = (!empty($t['poster']))
    ? "../../assets/uploads/" . $t['poster']
    : "https://via.placeholder.com/500x600";

// Status
$status_color = ($t['ticket_status'] === "verified") ? "#10b981" : "#f59e0b";

// QR Code
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($t['ticket_code']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Ticket - <?php echo htmlspecialchars($t['event_title']); ?></title>
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
body { padding-top:120px; background:#0f172a; }
.ticket-wrapper { max-width: 820px; margin:auto; }
.ticket-box {
    background:#0b1120; border-radius:18px; padding:28px;
    border:1px solid rgba(148,163,184,0.25);
    box-shadow:0 12px 35px rgba(0,0,0,0.65);
}
.ticket-grid { display:grid; grid-template-columns: 1.2fr 1fr; gap:25px; }
.ticket-left img { width:100%; border-radius:14px; object-fit:cover; max-height:330px; }
.ticket-right h2 { color:white; margin-bottom:8px; font-size:26px; }
.ticket-meta p { color:#cbd5e1; margin:5px 0; font-size:15px; }
.ticket-price { color:#fbbf24; font-size:24px; margin-top:10px; font-weight:700; }
.ticket-status {
    display:inline-block; padding:6px 12px; border-radius:999px;
    font-size:13px; font-weight:700; color:#0b1120;
    background:<?php echo $status_color; ?>; margin-top:6px;
}
.ticket-user p { margin:4px 0; color:#e2e8f0; }
.ticket-qr { text-align:center; margin-top:10px; }
.ticket-qr img { background:white; padding:10px; border-radius:12px; }
.ticket-code { color:white; font-size:15px; margin-top:10px; }
.ticket-note { text-align:center; margin-top:16px; font-size:13px; color:#94a3b8; }
.download-btn {
    margin-top:20px; padding:12px 22px; background:#6366f1;
    color:white; font-size:15px; border-radius:999px;
    border:none; cursor:pointer; display:flex; gap:8px; align-items:center;
}
.download-btn:hover { background:#4f46e5; }
@media(max-width:768px){
    .ticket-grid{ grid-template-columns:1fr; }
    body { padding-top:140px; }
}
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
        <a href="user_dashboard.php" class="nav-btn nav-btn-primary">My Bookings</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="ticket-wrapper">

<a href="user_dashboard.php" style="color:#94a3b8;">← Back</a><br><br>

<div class="ticket-box" id="ticketArea">

<div class="ticket-grid">

<div class="ticket-left">
    <img src="<?php echo $poster; ?>">
</div>

<div class="ticket-right">
    <h2><?php echo htmlspecialchars($t['event_title']); ?></h2>

    <div class="ticket-meta">
        <p><b>Date:</b> <?php echo $t['event_date']; ?></p>
        <p><b>Location:</b> <?php echo $t['city']; ?>, <?php echo $t['state']; ?></p>
        <p><b>Host:</b> <?php echo $t['host_name']; ?></p>
    </div>

    <div class="ticket-price">₹<?php echo $t['price']; ?></div>

    <p><b>Transaction:</b> <?php echo $t['transaction_id']; ?></p>

    <span class="ticket-status"><?php echo strtoupper($t['ticket_status']); ?></span>

    <div class="ticket-user">
        <p><b>Name:</b> <?php echo $t['uname']; ?></p>
        <p><b>Email:</b> <?php echo $t['uemail']; ?></p>
        <p><b>Phone:</b> <?php echo $t['uphone']; ?></p>
    </div>
</div>

</div>

<div class="ticket-qr">
    <img src="<?php echo $qr_url; ?>">
    <div class="ticket-code">Code: <?php echo $t['ticket_code']; ?></div>
</div>

<div class="ticket-note">
    Screenshot or download this ticket. Host will verify it at the venue.
</div>

</div>

<button class="download-btn" onclick="downloadTicket()">⬇ Download Ticket</button>

</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
function downloadTicket(){
    html2canvas(document.getElementById("ticketArea"), {
        backgroundColor: null,
        useCORS: true,
        scale: 2,
        scrollY: -window.scrollY
    }).then(canvas => {
        const link = document.createElement("a");
        link.download = "Zevent_Ticket_<?php echo $t['ticket_code']; ?>.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
    });
}
</script>

</body>
</html>
