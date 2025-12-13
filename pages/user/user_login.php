<?php
session_start();
include "../../includes/db_connect.php";

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $pass  = $_POST['password'];

    // Fetch user
    $q = mysqli_query($connect, "SELECT * FROM users WHERE email='$email' LIMIT 1");

    if ($q && mysqli_num_rows($q) == 1) {

        $user = mysqli_fetch_assoc($q);

        if (password_verify($pass, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];

            // redirect to MAIN homepage (correct path)
            header("Location: ../../index.php");
            exit;
        } 
        else {
            $msg = "Incorrect Password.";
        }

    } else {
        $msg = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login - Zevent</title>
<link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body>

<h2>User Login</h2>

<p style="color:red;"><?php echo $msg; ?></p>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="user_signup.php">Signup</a></p>

</body>
</html>
