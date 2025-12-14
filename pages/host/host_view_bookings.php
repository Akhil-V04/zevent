<?php
session_start();
include "../../includes/db_connect.php";

if (!isset($_SESSION['host_id'])) {
    header("Location: host_login.php");
    exit;
}

$host_id = $_SESSION['host_id'];

// event_id check
if (!isset($_GET['event_id'])) {
    die("Invalid Event ID");
}

$event_id = intval($_GET['event_id']);

// validate event ownership
$eventSQL = "
    SELECT event_title, poster 
    FROM events 
    WHERE event_id = $event_id AND host_id = $host_id
";

$eventResult = mysqli_query($connect, $eventSQL);

if (!$eventResult || mysqli_num_rows($eventResult) == 0) {
    die("Access Denied.");
}

$event = mysqli_fetch_assoc($eventResult);

// fetch bookings
$bookingSQL = "
    SELECT 
        r.reg_id, 
        r.ticket_code, 
        r.transaction_id, 
        r.ticket_status,
        u.name AS user_name, 
        u.email, 
        u.phone
    FROM registrations r
    JOIN users u ON u.user_id = r.user_id
    WHERE r.event_id = $event_id
    ORDER BY r.reg_id DESC
";

$bookings = mysqli_query($connect, $bookingSQL);
$total_bookings = mysqli_num_rows($bookings);
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Bookings - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .event-header {
            background: #1a1a2e;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(99, 102, 241, 0.2);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .event-poster-thumb {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #6366f1;
        }
        
        .event-info h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #f1f5f9;
        }
        
        .event-info p {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
        }
        
        .status-pill {
            padding: 6px 12px;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .verified { background: #10b981; }
        .pending { background: #f59e0b; }
        
        .verify-btn {
            color: white;
            background: #10b981;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: inline-block;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .verify-btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .verified-badge {
            color: #10b981;
            font-weight: bold;
            font-size: 14px;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .event-header {
                flex-direction: column;
                text-align: center;
            }
            
            .event-info h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

<!-- HOST NAVBAR -->
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

    <!-- EVENT HEADER -->
    <div class="event-header">
        <?php if (!empty($event['poster'])): ?>
            <img src="../../assets/uploads/<?php echo htmlspecialchars($event['poster']); ?>" 
                 class="event-poster-thumb" 
                 alt="Event Poster">
        <?php endif; ?>
        
        <div class="event-info">
            <h2><?php echo htmlspecialchars($event['event_title']); ?></h2>
            <p><strong><?php echo $total_bookings; ?></strong> Total Bookings</p>
        </div>
    </div>

    <!-- SUCCESS MESSAGE -->
    <?php if (isset($_GET['verified'])): ?>
        <div class="success-message">
            ✓ Ticket verified successfully!
        </div>
    <?php endif; ?>

    <!-- BACK BUTTON -->
    <a href="host_dashboard.php" class="nav-btn" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>

    <h3 class="section-title" style="font-size: 22px; margin: 20px 0;">Event Bookings</h3>

    <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
        
        <!-- THIS IS THE FIX - TABLE WRAPPER -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Transaction ID</th>
                        <th>Ticket Code</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset pointer to start
                    mysqli_data_seek($bookings, 0);
                    
                    while ($row = mysqli_fetch_assoc($bookings)): 
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            
                            <td>
                                <?php echo htmlspecialchars($row['email']); ?><br>
                                <span style="color: #94a3b8; font-size: 13px;">
                                    <?php echo htmlspecialchars($row['phone']); ?>
                                </span>
                            </td>
                            
                            <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['ticket_code']); ?></td>
                            
                            <td>
                                <span class="status-pill <?php echo $row['ticket_status']; ?>">
                                    <?php echo ucfirst($row['ticket_status']); ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($row['ticket_status'] == "pending"): ?>
                                    <a href="verify_ticket.php?reg_id=<?php echo $row['reg_id']; ?>&event_id=<?php echo $event_id; ?>" 
                                       class="verify-btn">
                                        Verify Ticket
                                    </a>
                                <?php else: ?>
                                    <span class="verified-badge">✔ Verified</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- END TABLE WRAPPER -->

    <?php else: ?>
        
        <div style="background: rgba(99, 102, 241, 0.1); padding: 30px; border-radius: 12px; text-align: center; margin-top: 20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 15px; opacity: 0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p style="font-size: 18px; color: #94a3b8; margin-bottom: 20px;">
                No bookings found for this event yet.
            </p>
            <a href="host_dashboard.php" class="btn-reset">Back to Dashboard</a>
        </div>
        
    <?php endif; ?>

</div>

<?php include "../../includes/footer.php"; ?>

</body>
</html>