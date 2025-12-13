<?php
include "../../includes/db_connect.php";

session_start();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $q = mysqli_query($connect, "SELECT * FROM hosts WHERE email='$email'");

    if (mysqli_num_rows($q) == 1) {
        $host = mysqli_fetch_assoc($q);

        if (password_verify($pass, $host['password'])) {
            $_SESSION['host_id'] = $host['host_id'];
            $_SESSION['host_name'] = $host['name'];

            header("Location: host_dashboard.php");
            exit;
        } else {
            $msg = "Invalid password.";
        }
    } else {
        $msg = "Host not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../../assets/css/style.css">

    <title>Host Login - Zevent</title>
</head>
<body>
<h2>Host Login</h2>

<?php if (isset($_GET['registered'])) echo "<p style='color:green;'>Signup successful. Please login.</p>"; ?>
<p style="color:red;"><?php echo $msg; ?></p>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <button type="submit">Login</button>
</form>

<p>Don't have a host account? <a href="host_signup.php">Signup</a></p>
</body>
</html>
