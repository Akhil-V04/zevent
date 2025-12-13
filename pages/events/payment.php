<?php
session_start();
include "../../includes/db_connect.php";

// USER LOGIN CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/user_login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "";

// EVENT ID CHECK
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("Invalid Event ID");
}

$event_id = intval($_GET['event_id']);

// FETCH EVENT DETAILS (UPI + QR FROM EVENTS TABLE)
$sql = "
    SELECT e.*, h.name AS host_name
    FROM events e
    JOIN hosts h ON e.host_id = h.host_id
    WHERE e.event_id = $event_id
    LIMIT 1
";

$res = mysqli_query($connect, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    die("Event not found.");
}

$event = mysqli_fetch_assoc($res);

// POSTER
$poster = (!empty($event['poster']))
    ? "../../assets/uploads/" . $event['poster']
    : "https://via.placeholder.com/400x500";

// QR IMAGE (CORRECT COLUMN)
$qr_img = (!empty($event['qr_image']))
    ? "../../assets/uploads/" . $event['qr_image']
    : "";

// UPI ID (CORRECT COLUMN)
$upi_id = $event['upi_id'] ?? "";

$msg = "";

// -------------------------
// HANDLE PAYMENT SUBMIT
// -------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $transaction = trim($_POST['transaction_id']);

    if ($transaction == "") {
        $msg = "Please enter the Transaction ID.";
    } else {

        $ticket_code = "TKT-" . strtoupper(substr(md5(time() . rand()), 0, 8));

        $insert = "
            INSERT INTO registrations 
            (event_id, user_id, ticket_code, transaction_id, ticket_status)
            VALUES 
            ($event_id, $user_id, '$ticket_code', '$transaction', 'pending')
        ";

        if (mysqli_query($connect, $insert)) {
            $id = mysqli_insert_id($connect);
            header("Location: ../user/view_ticket.php?id=$id");
            exit;
        } else {
            $msg = "Booking Failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment - <?php echo $event['event_title']; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        body { padding-top:110px; }

        .box {
            width: 700px;
            margin: auto;
            background: rgba(255,255,255,0.08);
            padding: 25px;
            border-radius: 14px;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            color: white;
        }

        .qr-box {
            background: rgba(255,255,255,0.12);
            padding: 18px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }

        .upi-tag {
            font-size: 18px;
            margin-top: 10px;
            font-weight: bold;
            color: #ffd369;
        }

        .pay-btn {
            padding: 12px 20px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .input {
            padding: 12px;
            width: 70%;
            border-radius: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid #475569;
            color: #fff;
        }
    </style>
</head>

<body>

<?php include "../user/user_navbar.php"; ?>

<div class="box">

    <h2><?php echo $event['event_title']; ?></h2>

    <img src="<?php echo $poster; ?>" style="width:100%; border-radius:10px;">

    <p><b>Date:</b> <?php echo $event['event_date']; ?></p>
    <p><b>Location:</b> <?php echo $event['city']; ?>, <?php echo $event['state']; ?></p>
    <p><b>Price:</b> ₹<?php echo $event['price']; ?></p>

    <hr><br>

    <h3>Make Payment</h3>

    <?php if (!empty($qr_img)): ?>
        <div class="qr-box">
            <img src="<?php echo $qr_img; ?>" style="width:260px; border-radius:12px;">
            <?php if (!empty($upi_id)): ?>
                <p class="upi-tag">UPI ID: <?php echo htmlspecialchars($upi_id); ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p style="color:#ffb3b3;">⚠ Host has not uploaded a QR code.</p>
    <?php endif; ?>

    <p>After payment, enter the Transaction ID below.</p>

    <p style="color:#ff6b6b;"><?php echo $msg; ?></p>

    <form method="post">
        <input type="text" name="transaction_id" class="input" placeholder="Enter Transaction ID" required>
        <br><br>
        <button class="pay-btn">Confirm Booking</button>
    </form>

</div>

</body>
</html>
