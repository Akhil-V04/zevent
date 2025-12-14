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
    $title     = mysqli_real_escape_string($connect, $_POST['title']);
    $category  = $_POST['category'];
    $price     = intval($_POST['price']);
    $desc      = mysqli_real_escape_string($connect, $_POST['description']);
    $city      = mysqli_real_escape_string($connect, $_POST['city']);
    $state     = mysqli_real_escape_string($connect, $_POST['state']);
    $event_date = $_POST['event_date'];
    $upi       = mysqli_real_escape_string($connect, $_POST['upi_id']);

    // Handle "Other" category
    if ($category === "Others" && !empty($_POST['other_category'])) {
        $category = mysqli_real_escape_string($connect, trim($_POST['other_category']));
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
        (host_id, event_title, category, poster, price, description, city, state, event_date, approval_status, upi_id, qr_code)
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .create-form-container {
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

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .create-form-container {
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
        <input type="text" name="search" placeholder="Search your events..." autocomplete="off">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">
        <a href="create_event.php" class="nav-btn nav-btn-primary">+ Create Event</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

    <div class="create-form-container">
        
        <h2 class="form-title">Create New Event</h2>

        <?php if ($msg): ?>
            <div class="error-message">
                <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <!-- EVENT TITLE -->
            <div class="form-group">
                <label>Event Title *</label>
                <input type="text" name="title" placeholder="Enter event title" required>
            </div>

            <!-- CATEGORY -->
            <div class="form-group">
                <label>Category *</label>
                <select name="category" id="category" onchange="toggleOther()" required>
                    <option value="">Select Category</option>
                    <option value="Concert">Concert</option>
                    <option value="Workshop">Workshop</option>
                    <option value="College Fest">College Fest</option>
                    <option value="Others">Others</option>
                </select>

                <div id="otherBox" style="display:none;">
                    <label>Custom Category</label>
                    <input type="text" name="other_category" placeholder="Enter custom category">
                </div>
            </div>

            <!-- POSTER -->
            <div class="form-group">
                <label>Event Poster *</label>
                <input type="file" name="poster" accept="image/*" required>
                <p class="file-info">Upload event poster (JPG, PNG, GIF)</p>
            </div>

            <!-- QR CODE -->
            <div class="form-group">
                <label>Payment QR Code (Optional)</label>
                <input type="file" name="qr_image" accept="image/*">
                <p class="file-info">Upload QR code for payment</p>
            </div>

            <!-- UPI ID -->
            <div class="form-group">
                <label>UPI ID *</label>
                <input type="text" name="upi_id" placeholder="example@upi" required>
            </div>

            <!-- TICKET PRICE -->
            <div class="form-group">
                <label>Ticket Price (₹) *</label>
                <input type="number" name="price" placeholder="Enter ticket price" min="0" required>
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group">
                <label>Event Description *</label>
                <textarea name="description" placeholder="Describe your event..." required></textarea>
            </div>

            <!-- CITY -->
            <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" placeholder="Enter city name" required>
            </div>

            <!-- STATE -->
            <div class="form-group">
                <label>State *</label>
                <input type="text" name="state" placeholder="Enter state name" required>
            </div>

            <!-- EVENT DATE -->
            <div class="form-group">
                <label>Event Date *</label>
                <input type="date" name="event_date" required>
            </div>

            <button type="submit" class="submit-btn">Create Event</button>

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