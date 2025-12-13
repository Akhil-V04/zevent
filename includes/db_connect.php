<?php
// Database Connection (GLOBAL FIX)

$connect = mysqli_connect(
    "sql206.ezyro.com",
    "ezyro_40657062",
    "21252125@bhavz",
    "ezyro_40657062_zevent"
);

if (!$connect) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// COMPATIBILITY FIX
// Some files use $conn, some use $connect
$conn = $connect;
?>
