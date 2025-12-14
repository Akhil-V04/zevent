<?php
session_start();
include "../../includes/db_connect.php";

// HOST AUTHENTICATION
if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id = $_SESSION['host_id'];

// VALIDATE EVENT ID
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("Invalid Event");
}

$event_id = intval($_GET['event_id']);

// FETCH EVENT DETAILS
$sql = "SELECT * FROM events WHERE event_id=$event_id AND host_id=$host_id LIMIT 1";
$res = mysqli_query($connect, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("You cannot edit this event.");
}

$event = mysqli_fetch_assoc($res);

// UPDATE EVENT (ON FORM SUBMIT)
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

    // POSTER UPDATE
    $poster = $event['poster'];
    if (!empty($_FILES['poster']['name'])) {
        $poster = time() . "_poster_" . basename($_FILES['poster']['name']);
        move_uploaded_file($_FILES['poster']['tmp_name'], "../../assets/uploads/" . $poster);
    }

    // QR CODE UPDATE
    $qr_code = $event['qr_code'];
    if (!empty($_FILES['qr_code']['name'])) {
        $qr_code = time() . "_qr_" . basename($_FILES['qr_code']['name']);
        move_uploaded_file($_FILES['qr_code']['tmp_name'], "../../assets/uploads/" . $qr_code);
    }

    // UPDATE QUERY
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .edit-form-container {
            max-width: 700px;
            margin: 0 auto;
            background: #1a1a2e;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid rgba(99, 102, 241, 0.2);
            background: #0f0f23;
            color: #f1f5f9;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .image-preview {
            width: 120px;
            height: 120px;
            margin-top: 10px;
            border-radius: 10px;
            border: 2px solid #6366f1;
            object-fit: cover;
        }
        
        .submit-btn {
            width: 100%;
            padding: 14px 28px;
            border-radius: 10px;
            border: none;
            background: #6366f1;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background: #818cf8;
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #818cf8;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
        }
        
        .back-link:hover {
            color: #a5b4fc;
        }
        
        #otherBox {
            margin-top: 15px;
        }
        
        .file-info {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .edit-form-container {
                padding: 20px 15px;
            }
            
            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<header class="nav-glass">
    <div class="nav-left">
        <div class="logo">⚡ Zevent Host</div>
    </div>

    <form method="GET" class="search-box" action="host_dashboard.php">
        <input type="text" name="search" placeholder="Search your events...">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">
        <a href="create_event.php" class="nav-btn nav-btn-primary">+ Create Event</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

    <div class="edit-form-container">
        
        <h2 class="form-title">Edit Event</h2>

        <form method="POST" enctype="multipart/form-data">

            <!-- TITLE -->
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($event['event_title']); ?>" required>
            </div>

            <!-- CATEGORY -->
            <div class="form-group">
                <label>Category</label>
                <select name="category" id="category" onchange="toggleOther()" required>
                    <option value="Concert" <?php if($event['category']=="Concert") echo "selected"; ?>>Concert</option>
                    <option value="Workshop" <?php if($event['category']=="Workshop") echo "selected"; ?>>Workshop</option>
                    <option value="College Fest" <?php if($event['category']=="College Fest") echo "selected"; ?>>College Fest</option>
                    <option value="Others" <?php if(!in_array($event['category'], ["Concert","Workshop","College Fest"])) echo "selected"; ?>>Others</option>
                </select>

                <div id="otherBox" style="display:<?php echo (!in_array($event['category'], ["Concert","Workshop","College Fest"])) ? 'block':'none'; ?>;">
                    <label>Custom Category</label>
                    <input type="text" name="other_category" value="<?php echo htmlspecialchars($event['category']); ?>">
                </div>
            </div>

            <!-- POSTER -->
            <div class="form-group">
                <label>Event Poster</label>
                <input type="file" name="poster" accept="image/*">
                <p class="file-info">Current poster:</p>
                <?php if (!empty($event['poster'])): ?>
                    <img src="../../assets/uploads/<?php echo htmlspecialchars($event['poster']); ?>" class="image-preview" alt="Current Poster">
                <?php endif; ?>
            </div>

            <!-- QR CODE -->
            <div class="form-group">
                <label>Payment QR Code</label>
                <input type="file" name="qr_code" accept="image/*">
                <?php if (!empty($event['qr_code'])): ?>
                    <p class="file-info">Current QR code:</p>
                    <img src="../../assets/uploads/<?php echo htmlspecialchars($event['qr_code']); ?>" class="image-preview" alt="Current QR Code">
                <?php endif; ?>
            </div>

            <!-- PRICE -->
            <div class="form-group">
                <label>Ticket Price (₹)</label>
                <input type="number" name="price" value="<?php echo htmlspecialchars($event['price']); ?>" min="0" required>
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <!-- CITY -->
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($event['city']); ?>" required>
            </div>

            <!-- STATE -->
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" value="<?php echo htmlspecialchars($event['state']); ?>" required>
            </div>

            <!-- DATE -->
            <div class="form-group">
                <label>Event Date</label>
                <input type="date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>

            <button type="submit" class="submit-btn">Update Event</button>

        </form>

        <a href="host_dashboard.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>

    </div>

</div>

<?php include "../../includes/footer.php"; ?>

<script>
function toggleOther() {
    const box = document.getElementById("otherBox");
    box.style.display = (document.getElementById("category").value === "Others") ? "block" : "none";
}
</script>

</body>
</html>