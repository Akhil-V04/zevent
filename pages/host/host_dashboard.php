<?php
session_start();
include "../../includes/db_connect.php";

// Check host session
if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id   = $_SESSION['host_id'];
$host_name = $_SESSION['host_name'] ?? "Host";
$msg = "";

// DELETE EVENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event_id'])) {
    $delete_event_id = intval($_POST['delete_event_id']);

    $deleteSQL = "DELETE FROM events WHERE event_id = $delete_event_id AND host_id = $host_id";

    if (mysqli_query($connect, $deleteSQL)) {
        $msg = "Event deleted successfully.";
    } else {
        $msg = "Error deleting event: " . mysqli_error($connect);
    }
}

// STAT CARDS
$total_events = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total FROM events WHERE host_id = $host_id"
))['total'];

$total_bookings = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total 
     FROM registrations r 
     JOIN events e ON r.event_id = e.event_id 
     WHERE e.host_id = $host_id"
))['total'];

$pending_verifications = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total 
     FROM registrations r 
     JOIN events e ON r.event_id = e.event_id 
     WHERE e.host_id = $host_id AND r.ticket_status='pending'"
))['total'];

$verified_tickets = mysqli_fetch_assoc(mysqli_query(
    $connect,
    "SELECT COUNT(*) AS total 
     FROM registrations r 
     JOIN events e ON r.event_id = e.event_id 
     WHERE e.host_id = $host_id AND r.ticket_status='verified'"
))['total'];

// SEARCH FILTER
$search = "";
$searchCondition = "";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = mysqli_real_escape_string($connect, trim($_GET['search']));
    $searchCondition = " AND e.event_title LIKE '%$search%' ";
}

// EVENT LIST
$sql = "
    SELECT 
        e.event_id,
        e.event_title,
        e.category,
        e.poster,
        e.event_date,
        e.approval_status,
        (
            SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id
        ) AS total_bookings
    FROM events e
    WHERE e.host_id = $host_id
    $searchCondition
    ORDER BY e.event_id DESC
";

$events = mysqli_query($connect, $sql);

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Dashboard - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        /* Poster size fix */
        .poster-small {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            object-fit: cover;
            display: block;
        }

        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1a1a2e;
            color: white;
            padding: 25px 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(99, 102, 241, 0.2);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #6366f1;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card p {
            font-size: 36px;
            font-weight: bold;
            color: #6366f1;
            margin: 0;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            text-transform: capitalize;
        }
        
        .approved { background: #10b981; }
        .pending  { background: #f59e0b; }
        .rejected { background: #ef4444; }

        .action-btns {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 12px;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
            border: none;
            cursor: pointer;
        }
        
        .blue  { background: #0ea5e9; }
        .blue:hover { background: #0284c7; }
        
        .red   { background: #ef4444; }
        .red:hover { background: #dc2626; }
        
        .green { background: #10b981; }
        .green:hover { background: #059669; }
        
        .message-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .message-info {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }
        
        /* Mobile responsive stats */
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px 15px;
            }
            
            .stat-card h3 {
                font-size: 12px;
            }
            
            .stat-card p {
                font-size: 28px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
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

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search your events..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
        <button type="submit">Search</button>
    </form>

    <div class="nav-right">
        <a href="create_event.php" class="nav-btn nav-btn-primary">+ Create Event</a>
        <a href="../logout.php" class="nav-btn nav-btn-host">Logout</a>
    </div>
</header>

<div class="container">

    <h2 class="welcome-text">Welcome, <?php echo htmlspecialchars($host_name); ?></h2>

    <?php if ($msg != ""): ?>
        <div class="message-box message-info">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['event_created'])): ?>
        <div class="message-box message-success">
            ✓ Event created successfully. Awaiting admin approval.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['event_updated'])): ?>
        <div class="message-box message-success">
            ✓ Event updated successfully. Awaiting re-approval.
        </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-container">
        <div class="stat-card">
            <h3>Total Events</h3>
            <p><?php echo $total_events; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Bookings</h3>
            <p><?php echo $total_bookings; ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending Tickets</h3>
            <p><?php echo $pending_verifications; ?></p>
        </div>
        <div class="stat-card">
            <h3>Verified Tickets</h3>
            <p><?php echo $verified_tickets; ?></p>
        </div>
    </div>

    <!-- EVENTS TABLE -->
    <h3 class="section-title" style="font-size: 24px; margin: 30px 0 20px 0;">Your Events</h3>

    <?php if ($events && mysqli_num_rows($events) > 0): ?>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ev = mysqli_fetch_assoc($events)):
                        $poster = (!empty($ev['poster']))
                            ? "../../assets/uploads/" . $ev['poster']
                            : "https://via.placeholder.com/100x140";

                        $badge = $ev['approval_status'];
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($poster); ?>" class="poster-small" alt="Event Poster">
                        </td>
                        <td><?php echo htmlspecialchars($ev['event_title']); ?></td>
                        <td><?php echo htmlspecialchars($ev['category']); ?></td>
                        <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $badge; ?>">
                                <?php echo ucfirst($badge); ?>
                            </span>
                        </td>
                        <td><?php echo $ev['total_bookings']; ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="host_view_bookings.php?event_id=<?php echo $ev['event_id']; ?>" class="action-btn blue">Bookings</a>
                                <a href="edit_event.php?event_id=<?php echo $ev['event_id']; ?>" class="action-btn green">Edit</a>
                                <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="delete_event_id" value="<?php echo $ev['event_id']; ?>">
                                    <button type="submit" class="action-btn red">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        
        <div style="background: rgba(99, 102, 241, 0.1); padding: 30px; border-radius: 12px; text-align: center; margin-top: 20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 15px; opacity: 0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p style="font-size: 18px; color: #94a3b8; margin-bottom: 20px;">
                <?php echo $search ? "No events found matching '$search'" : "You have not created any events yet."; ?>
            </p>
            <?php if ($search): ?>
                <a href="host_dashboard.php" class="btn-reset">Clear Search</a>
            <?php else: ?>
                <a href="create_event.php" class="btn-reset">Create Your First Event</a>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>

</div>

<?php include "../../includes/footer.php"; ?>

</body>
</html>