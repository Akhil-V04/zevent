<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $host_id   = $_SESSION['host_id'];
    $title     = $_POST['title'];
    $category  = $_POST['category'];
    $price     = $_POST['price'];
    $desc      = $_POST['description'];
    $city      = $_POST['city'];
    $state     = $_POST['state'];
    $event_date = $_POST['event_date'];
    $upi       = $_POST['upi_id'];

    // Handle "Other" category
    if ($category === "Others" && !empty($_POST['other_category'])) {
        $category = trim($_POST['other_category']);
    }

    // Poster Upload
    $posterName = "";
    if (!empty($_FILES['poster']['name'])) {
        $posterName = time() . "_" . basename($_FILES['poster']['name']);
        move_uploaded_file(
            $_FILES['poster']['tmp_name'],
            "../../assets/uploads/" . $posterName
        );
    }

    // QR Upload (Optional)
    $qrName = "";
    if (!empty($_FILES['qr_image']['name'])) {
        $qrName = "QR_" . time() . "_" . basename($_FILES['qr_image']['name']);
        move_uploaded_file(
            $_FILES['qr_image']['tmp_name'],
            "../../assets/uploads/" . $qrName
        );
    }

    // Insert Event
    $sql = "
        INSERT INTO events 
        (host_id, event_title, category, poster, price, description, city, state, event_date, approval_status, upi_id, qr_image)
        VALUES
        ('$host_id', '$title', '$category', '$posterName', '$price', '$desc', '$city', '$state', '$event_date', 'pending', '$upi', '$qrName')
    ";

    if (mysqli_query($connect, $sql)) {
        header("Location: host_dashboard.php?event_created=1");
        exit;
    } else {
        $msg = "Error creating event: " . mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<h2>Create Event</h2>

<p style="color:red;"><?php echo $msg; ?></p>

<form method="post" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Event Title" required><br><br>

    <!-- CATEGORY -->
    <select name="category" id="category" onchange="toggleOther()" required>
        <option value="">Select Category</option>
        <option value="Concert">Concert</option>
        <option value="Workshop">Workshop</option>
        <option value="College Fest">College Fest</option>
        <option value="Others">Others</option>
    </select>
    <br><br>

    <div id="otherBox" style="display:none;">
        <input type="text" name="other_category" placeholder="Enter Category">
        <br><br>
    </div>

    <script>
        function toggleOther() {
            let c = document.getElementById("category").value;
            document.getElementById("otherBox").style.display = (c === "Others") ? "block" : "none";
        }
    </script>

    <label>Poster:</label><br>
    <input type="file" name="poster" required><br><br>

    <label>QR Code (optional):</label><br>
    <input type="file" name="qr_image"><br><br>

    <input type="text" name="upi_id" placeholder="UPI ID"><br><br>

    <input type="number" name="price" placeholder="Ticket Price" required><br><br>

    <textarea name="description" placeholder="Description" required></textarea><br><br>

    <input type="text" name="city" placeholder="City" required><br><br>
    <input type="text" name="state" placeholder="State" required><br><br>

    <!-- FIXED DATE INPUT -->
    <label>Event Date:</label><br>
    <input type="date" name="event_date" required><br><br>

    <button type="submit">Create Event</button>

</form>

</body>
</html>
