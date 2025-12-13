<?php
include "../../includes/db_connect.php";

session_start();

$msg = "";

// create default admin if none exists
$checkAdmin = mysqli_query($conn, "SELECT * FROM admin LIMIT 1");
if ($checkAdmin && mysqli_num_rows($checkAdmin) == 0) {
    $defaultPass = password_hash("admin123", PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO admin (name,email,password) VALUES ('Super Admin','admin@zevent.com','$defaultPass')");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM admin WHERE email='$email'");
    if ($q && mysqli_num_rows($q) == 1) {
        $admin = mysqli_fetch_assoc($q);

        if (password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $msg = "Invalid password.";
        }
    } else {
        $msg = "Admin not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - ⚡ Zevent</title>
<link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body>
<h2>Admin Login</h2>
<p style="color:red;"><?php echo $msg; ?></p>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>
</body>
</html>
