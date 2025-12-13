<?php
session_start();
$connect = mysqli_connect(
    "sql206.ezyro.com",
    "ezyro_40657062",
    "21252125@bhavz",
    "ezyro_40657062_zevent"
);

if (!$connect) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// ---------------------------
// USER AUTH CHECK
// ---------------------------
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['user_name'] : "";

// ---------------------------
// CATEGORY LOGIC
// ---------------------------
$defaultCategories = ["Concert", "Workshop", "College Fest", "Others"];

$catQuery = mysqli_query($connect, "SELECT DISTINCT category FROM events WHERE approval_status='approved'");
$dbCategories = [];

if ($catQuery && mysqli_num_rows($catQuery) > 0) {
    while ($row = mysqli_fetch_assoc($catQuery)) {
        $dbCategories[] = $row['category'];
    }
}

$allCategories = array_unique(array_merge($defaultCategories, $dbCategories));

// ---------------------------
// SEARCH + FILTERS
// ---------------------------
$searchTerm = $_GET['search'] ?? "";
$selectedCategory = $_GET['category'] ?? "";

$conditions = ["approval_status='approved'"];

if ($searchTerm !== "") {
    $s = mysqli_real_escape_string($connect, $searchTerm);
    $conditions[] = "(event_title LIKE '%$s%' OR category LIKE '%$s%' OR city LIKE '%$s%')";
}

if ($selectedCategory !== "") {
    $cat = mysqli_real_escape_string($connect, $selectedCategory);
    $conditions[] = "LOWER(category) = LOWER('$cat')";
}

$where = implode(" AND ", $conditions);
$query = "SELECT * FROM events WHERE $where ORDER BY event_id DESC";

$events = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zevent</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="nav-glass">

    <div class="nav-left">
        <div class="logo">⚡ Zevent</div>
    </div>

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search events, cities, categories..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">

        <?php if ($user_logged_in): ?>

            <a href="pages/user/user_dashboard.php" class="nav-btn nav-btn-primary">
                My Bookings
            </a>

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

                    <div class="profile-divider"></div>

                    <a href="pages/user/user_profile.php" class="profile-link">My Details</a>
                    <a href="pages/logout.php" class="profile-link profile-link-danger">Logout</a>
                </div>
            </div>

        <?php else: ?>

            <a href="pages/auth/login.php" class="nav-btn nav-btn-primary">Login</a>

        <?php endif; ?>

      <a href="pages/auth/login.php" class="nav-btn nav-btn-host">
    Become a Host
</a>


    </div>
</header>

<script>
function toggleProfileMenu() {
    document.getElementById("profileMenu").classList.toggle("show");
}
</script>

<!-- ========================= -->
<!--       CONTENT AREA        -->
<!-- ========================= -->
<div class="container">

    <!-- ✅ ONLY ADDED BLOCK -->
    <?php if ($user_logged_in): ?>
        <h3 style="color:#e5e7eb; margin-bottom:15px;">
            Hello, <?php echo htmlspecialchars($user_name); ?>
        </h3>
    <?php endif; ?>

    <h2 class="section-title">Discover Events Near You</h2>

    <div class="chips-container">

        <a href="index.php" class="chip <?php echo ($selectedCategory == "") ? 'chip-active' : ''; ?>">
            All
        </a>

        <?php foreach ($allCategories as $cat): ?>
            <a href="index.php?category=<?php echo urlencode($cat); ?>"
               class="chip <?php echo ($selectedCategory == $cat) ? 'chip-active' : ''; ?>">
                <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>

    </div>

    <div class="event-grid">

        <?php if ($events && mysqli_num_rows($events) > 0): ?>
            <?php while ($ev = mysqli_fetch_assoc($events)): ?>

                <?php
                $img = !empty($ev['poster'])
                    ? "assets/uploads/" . htmlspecialchars($ev['poster'])
                    : "https://via.placeholder.com/300x450";
                ?>

                <a href="pages/events/event_details.php?id=<?php echo $ev['event_id']; ?>" class="event-card">

                    <div class="event-img-box">
                        <img src="<?php echo $img; ?>" class="event-img">
                        <div class="price-tag">₹<?php echo htmlspecialchars($ev['price']); ?></div>
                        <div class="book-overlay">Book Now →</div>
                    </div>

                    <h3 class="event-title"><?php echo htmlspecialchars($ev['event_title']); ?></h3>
                    <p class="event-meta">
                        <span><?php echo htmlspecialchars($ev['category']); ?></span> •
                        <span><?php echo htmlspecialchars($ev['event_date']); ?></span>
                    </p>

                </a>

            <?php endwhile; ?>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>

    </div>

</div>
<?php include "includes/footer.php"; ?>
</body>
</html>
