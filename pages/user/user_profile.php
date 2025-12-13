<?php
session_start();
include "../../includes/db_connect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// HANDLE PROFILE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = mysqli_real_escape_string($connect, $_POST['name']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);

    $update = "
        UPDATE users 
        SET name='$name', phone='$phone'
        WHERE user_id=$user_id
    ";
    mysqli_query($connect, $update);

    // Redirect to home page after save
    header("Location: ../../index.php");
    exit;
}

// Fetch user details
$sql = "SELECT * FROM users WHERE user_id = $user_id LIMIT 1";
$res = mysqli_query($connect, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("User not found.");
}

$user = mysqli_fetch_assoc($res);
$user_name  = $user['name'];
$user_email = $user['email'];
$user_phone = $user['phone'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        body { padding-top:110px; }

        .back-home-btn {
            display:inline-block;
            margin-bottom:18px;
            font-size:15px;
            color:#818cf8;
            text-decoration:none;
            font-weight:600;
        }
        .back-home-btn:hover { color:#a5b4fc; }

        .profile-wrapper {
            max-width:700px;
            margin:auto;
        }

        .profile-card {
            background:#0b1120;
            border-radius:16px;
            padding:30px;
            border:1px solid rgba(148,163,184,0.35);
            box-shadow:0 18px 45px rgba(15,23,42,0.5);
        }

        .profile-header-top {
            text-align:center;
            margin-bottom:25px;
        }

        .profile-header-top img {
            width:120px;
            height:120px;
            border-radius:50%;
            border:3px solid #6366f1;
            margin-bottom:10px;
        }

        .profile-header-top h2 {
            color:#f1f5f9;
            margin-bottom:5px;
        }

        .profile-header-top p {
            color:#9ca3af;
            font-size:14px;
        }

        .profile-form label {
            display:block;
            margin:10px 0 6px;
            font-size:15px;
            color:#e5e7eb;
        }

        .profile-input {
            width:100%;
            padding:12px;
            border-radius:10px;
            border:1px solid rgba(148,163,184,0.3);
            background:#1e293b;
            color:white;
            font-size:15px;
            outline:none;
        }

        .profile-input:focus {
            border-color:#6366f1;
            background:#111827;
        }

        .save-btn {
            margin-top:20px;
            padding:12px 20px;
            border-radius:10px;
            border:none;
            cursor:pointer;
            background:#6366f1;
            color:white;
            font-weight:600;
            width:100%;
            font-size:16px;
        }

        .save-btn:hover { background:#818cf8; }
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
        <a href="user_dashboard.php" class="nav-btn nav-btn-primary">My Bookings</a>

        <div class="profile-menu">
            <div class="profile-icon" onclick="toggleProfileMenu()">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true">
            </div>

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

        <a href="../host/host_login.php" class="nav-btn nav-btn-host">Become a Host</a>
    </div>
</header>

<script>
function toggleProfileMenu() {
    document.getElementById("profileMenu").classList.toggle("show");
}
</script>

<!-- PROFILE CONTENT -->
<div class="container profile-wrapper">
    <a href="../../index.php" class="back-home-btn">← Back to Home</a>

    <div class="profile-card">

        <div class="profile-header-top">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true">
            <h2><?php echo htmlspecialchars($user_name); ?></h2>
            <p>Your personal details</p>
        </div>

        <form class="profile-form" method="POST">
            <label>Name</label>
            <input type="text" name="name" class="profile-input" value="<?php echo htmlspecialchars($user_name); ?>">

            <label>Email</label>
            <input type="email" class="profile-input" value="<?php echo htmlspecialchars($user_email); ?>" disabled>

            <label>Phone</label>
            <input type="text" name="phone" class="profile-input" value="<?php echo htmlspecialchars($user_phone); ?>">

            <button class="save-btn">Save Changes</button>
        </form>

    </div>
</div>
<?php include "../../includes/footer.php"; ?>

</body>
</html>
