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

/* ========================= */
/* USER AUTH CHECK           */
/* ========================= */
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['user_name'] : "";

/* ========================= */
/* CATEGORY LOGIC            */
/* ========================= */
$defaultCategories = ["Concert", "Workshop", "College Fest", "Others"];

$catQuery = mysqli_query(
    $connect,
    "SELECT DISTINCT category FROM events WHERE approval_status='approved'"
);

$dbCategories = [];
if ($catQuery && mysqli_num_rows($catQuery) > 0) {
    while ($row = mysqli_fetch_assoc($catQuery)) {
        $dbCategories[] = $row['category'];
    }
}

$allCategories = array_unique(array_merge($defaultCategories, $dbCategories));

/* ========================= */
/* SEARCH + FILTERS          */
/* ========================= */
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zevent - Discover Events Near You</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<!-- ========================= -->
<!-- NAVBAR                   -->
<!-- ========================= -->
<header class="nav-glass">

    <div class="nav-left">
        <div class="logo">⚡ Zevent</div>
    </div>

    <form method="GET" class="search-box">
        <input
            type="text"
            name="search"
            placeholder="Search events, cities, categories..."
            value="<?php echo htmlspecialchars($searchTerm); ?>"
            autocomplete="off"
        >
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">

        <?php if ($user_logged_in): ?>

            <a href="pages/user/user_dashboard.php" class="nav-btn nav-btn-primary">
                My Bookings
            </a>

            <!-- PROFILE DROPDOWN -->
            <div class="profile-menu">
                <div class="profile-icon" id="profileIconBtn">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true" alt="Profile">
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-header">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=6366f1&color=fff&bold=true" alt="Profile">
                        <div class="profile-info">
                            <p class="profile-name"><?php echo htmlspecialchars($user_name); ?></p>
                            <p class="profile-email">User Account</p>
                        </div>
                    </div>

                    <div class="profile-divider"></div>

                    <a href="pages/user/user_profile.php" class="profile-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        My Details
                    </a>

                    <div class="profile-divider"></div>

                    <a href="pages/logout.php" class="profile-link profile-link-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                </div>
            </div>

        <?php else: ?>

            <a href="pages/auth/login.php" class="nav-btn nav-btn-primary">
                Login
            </a>

        <?php endif; ?>

        <a href="pages/auth/login.php" class="nav-btn nav-btn-host">
            Become a Host
        </a>

    </div>
</header>

<script>
// Profile dropdown toggle
const profileIcon = document.getElementById('profileIconBtn');
const profileDropdown = document.getElementById('profileDropdown');

if (profileIcon && profileDropdown) {
    profileIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!profileIcon.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove('show');
        }
    });

    // Prevent dropdown from closing when clicking inside it
    profileDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}
</script>

<!-- ========================= -->
<!-- CONTENT AREA             -->
<!-- ========================= -->
<div class="container">

<?php if ($user_logged_in): ?>
    <h3 class="hello-text">Hello, <?php echo htmlspecialchars($user_name); ?></h3>
<?php endif; ?>

<h2 class="section-title">Discover Events Near You</h2>

<div class="chips-container">
    <a href="index.php" class="chip <?php echo ($selectedCategory == "") ? 'chip-active' : ''; ?>">
        All
    </a>

    <?php foreach ($allCategories as $cat): ?>
        <a
            href="index.php?category=<?php echo urlencode($cat); ?>"
            class="chip <?php echo ($selectedCategory == $cat) ? 'chip-active' : ''; ?>"
        >
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

<?php endwhile; ?>
<?php else: ?>

<div class="no-events">
    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <p>No events found matching your criteria.</p>
    <a href="index.php" class="btn-reset">View All Events</a>
</div>

<?php endif; ?>

</div>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>