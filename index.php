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

<!-- ========================= -->
<!--     MODERN NAVBAR         -->
<!-- ========================= -->
<header class="nav-glass">

    <!-- LEFT SIDE -->
    <div class="nav-left">
        <div class="logo">⚡ Zevent</div>
    </div>

    <!-- CENTER - SEARCH BAR -->
    <form method="GET" class="search-box">
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" name="search" placeholder="Search events, cities, categories..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- RIGHT SIDE -->
    <div class="nav-right">

        <!-- SHOW ONLY IF LOGGED IN -->
        <?php if ($user_logged_in): ?>

            <!-- MY BOOKINGS BUTTON -->
            <a href="pages/user/user_dashboard.php" class="nav-btn nav-btn-primary">
                My Bookings
            </a>

            <!-- PROFILE DROPDOWN MENU -->
            <div class="profile-menu">
                
                <!-- PROFILE ICON -->
                <div class="profile-icon" onclick="toggleProfileMenu()">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true">
                </div>

                <!-- DROPDOWN -->
                <div class="profile-dropdown" id="profileMenu">

                    <div class="profile-header">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true">
                        <div>
                            <p class="profile-name"><?php echo htmlspecialchars($user_name); ?></p>
                            <p class="profile-email">User Account</p>
                        </div>
                    </div>

                    <div class="profile-divider"></div>

                    <!-- NEW: MY DETAILS -->
                    <a href="pages/user/user_profile.php" class="profile-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>
                        My Details
                    </a>

                    <!-- LOGOUT -->
                    <a href="pages/logout.php" class="profile-link profile-link-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>

        <?php else: ?>

            <!-- IF USER NOT LOGGED IN -->
            <a href="pages/user/user_login.php" class="nav-btn nav-btn-primary">Login</a>

        <?php endif; ?>

        <!-- HOST BUTTON -->
        <a href="pages/host/host_login.php" class="nav-btn nav-btn-host">
            Become a Host
        </a>

    </div>

</header>

<script>
function toggleProfileMenu() {
    document.getElementById("profileMenu").classList.toggle("show");
}

window.onclick = function(event) {
    if (!event.target.closest(".profile-menu")) {
        let dropdown = document.getElementById("profileMenu");
        dropdown.classList.remove("show");
    }
}
</script>


<!-- ========================= -->
<!--       CONTENT AREA        -->
<!-- ========================= -->
<div class="container">

    <h2 class="section-title">Discover Events Near You</h2>

    <!-- CATEGORY CHIPS -->
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

    <!-- EVENTS GRID -->
    <div class="event-grid">

        <?php 
        if ($events && mysqli_num_rows($events) > 0):
            while ($ev = mysqli_fetch_assoc($events)):

                $img = !empty($ev['poster'])
                    ? "assets/uploads/" . htmlspecialchars($ev['poster'])
                    : "https://via.placeholder.com/300x450";
        ?>

        <a href="pages/events/event_details.php?id=<?php echo $ev['event_id']; ?>" class="event-card">

            <div class="event-img-box">
                <img src="<?php echo $img; ?>" class="event-img" alt="<?php echo htmlspecialchars($ev['event_title']); ?>">
                <div class="price-tag">₹<?php echo htmlspecialchars($ev['price']); ?></div>
                <div class="book-overlay">Book Now →</div>
            </div>

            <h3 class="event-title"><?php echo htmlspecialchars($ev['event_title']); ?></h3>
            <p class="event-meta">
                <span><?php echo htmlspecialchars($ev['category']); ?></span> • 
                <span><?php echo htmlspecialchars($ev['event_date']); ?></span>
            </p>

        </a>

        <?php endwhile; else: ?>

        <div class="no-events">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p>No events found matching your criteria.</p>
            <a href="index.php" class="btn-reset">View All Events</a>
        </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>