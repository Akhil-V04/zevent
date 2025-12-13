<?php
session_start();
include "../../includes/db_connect.php";

// ----------------------------
// HOST AUTHENTICATION
// ----------------------------
if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id = $_SESSION['host_id'];

// ----------------------------
// VALIDATE EVENT ID
// ----------------------------
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("Invalid Event");
}

$event_id = intval($_GET['event_id']);

// ----------------------------
// FETCH EVENT DETAILS
// ----------------------------
$sql = "SELECT * FROM events WHERE event_id=$event_id AND host_id=$host_id LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("You cannot edit this event.");
}

$event = mysqli_fetch_assoc($res);

// ----------------------------
// UPDATE EVENT (ON FORM SUBMIT)
// ----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title     = mysqli_real_escape_string($connect, $_POST['title']);
    $category  = $_POST['category'];
    if ($category === "Others") {
        $category = mysqli_real_escape_string($connect, $_POST['other_category']);
    }

    $price     = $_POST['price'];
    $desc      = mysqli_real_escape_string($connect, $_POST['description']);
    $city      = $_POST['city'];
    $state     = $_POST['state'];
    $event_date = $_POST['event_date'];

    // ------------------------------------
    // POSTER UPDATE
    // ------------------------------------
    $poster = $event['poster'];
    if (!empty($_FILES['poster']['name'])) {
        $poster = time() . "_poster_" . basename($_FILES['poster']['name']);
        move_uploaded_file($_FILES['poster']['tmp_name'], "../../assets/uploads/" . $poster);
    }

    // ------------------------------------
    // QR CODE UPDATE
    // ------------------------------------
    $qr_code = $event['qr_code'];
    if (!empty($_FILES['qr_code']['name'])) {
        $qr_code = time() . "_qr_" . basename($_FILES['qr_code']['name']);
        move_uploaded_file($_FILES['qr_code']['tmp_name'], "../../assets/uploads/" . $qr_code);
    }

    // ------------------------------------
    // UPDATE QUERY
    // ------------------------------------
    $update = "
        UPDATE events SET 
            event_title='$title',
            category='$category',
            poster='$poster',
            qr_code='$qr_code',
            price='$price',
            description='$desc',
            city='$city',
            state='$state',
            event_date='$event_date',
            approval_status='pending'
        WHERE event_id=$event_id AND host_id=$host_id
    ";

    mysqli_query($connect, $update);

    header("Location: host_dashboard.php?event_updated=1");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        body {
            padding-top: 110px;
            background: #0f172a;
            color: white;
        }
        .form-box {
            width: 700px;
            margin: auto;
            background: rgba(255,255,255,0.08);
            padding: 25px;
            border-radius: 12px;
            backdrop-filter: blur(8px);
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid #475569;
            color: white;
        }
        button {
            margin-top: 20px;
            padding: 12px 20px;
            border: none;
            background: #4f46e5;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        img.preview {
            width: 120px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #666;
        }
    </style>
</head>

<body>

<div class="form-box">

<h2>Edit Event</h2>

<form method="post" enctype="multipart/form-data">

    <!-- TITLE -->
    <label>Event Title</label>
    <input type="text" name="title" value="<?php echo $event['event_title']; ?>" required>

    <!-- CATEGORY -->
    <label>Category</label>
    <select name="category" id="category" onchange="toggleOther()">
        <option value="Concert"      <?php if($event['category']=="Concert") echo "selected"; ?>>Concert</option>
        <option value="Workshop"     <?php if($event['category']=="Workshop") echo "selected"; ?>>Workshop</option>
        <option value="College Fest" <?php if($event['category']=="College Fest") echo "selected"; ?>>College Fest</option>
        <option value="Others"       <?php if(!in_array($event['category'], ["Concert","Workshop","College Fest"])) echo "selected"; ?>>Others</option>
    </select>

    <div id="otherBox" style="display:<?php echo (!in_array($event['category'], ["Concert","Workshop","College Fest"])) ? 'block':'none'; ?>;">
        <label>Custom Category</label>
        <input type="text" name="other_category" value="<?php echo $event['category']; ?>">
    </div>

    <!-- POSTER -->
    <label>Event Poster</label>
    <input type="file" name="poster">
    <img src="../../assets/uploads/<?php echo $event['poster']; ?>" class="preview">

    <!-- QR CODE -->
    <label>Payment QR Code</label>
    <input type="file" name="qr_code">
    <?php if (!empty($event['qr_code'])): ?>
        <img src="../../assets/uploads/<?php echo $event['qr_code']; ?>" class="preview">
    <?php endif; ?>

    <!-- PRICE -->
    <label>Ticket Price (₹)</label>
    <input type="number" name="price" value="<?php echo $event['price']; ?>" required>

    <!-- DESCRIPTION -->
    <label>Description</label>
    <textarea name="description" rows="4" required><?php echo $event['description']; ?></textarea>

    <!-- CITY -->
    <label>City</label>
    <input type="text" name="city" value="<?php echo $event['city']; ?>" required>

    <!-- STATE -->
    <label>State</label>
    <input type="text" name="state" value="<?php echo $event['state']; ?>" required>

    <!-- DATE (FIXED) -->
    <label>Event Date</label>
    <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>

    <button type="submit">Update Event</button>

</form>

<br>
<a href="host_dashboard.php" style="color:#fff; text-decoration:underline;">← Back to Dashboard</a>

</div>

<script>
function toggleOther() {
    const box = document.getElementById("otherBox");
    box.style.display = (document.getElementById("category").value === "Others") ? "block" : "none";
}
</script>

</body>
</html>
