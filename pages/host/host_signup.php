<?php
session_start();
include "../../includes/db_connect.php";

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = mysqli_real_escape_string($connect, $_POST['name']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);
    $upi   = mysqli_real_escape_string($connect, $_POST['upi']);
    $pass  = $_POST['password'];

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Check email exists
    $check = mysqli_query($connect, "SELECT * FROM hosts WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "Email already registered.";
    } else {

        $q = "
            INSERT INTO hosts (name,email,password,phone,upi_id)
            VALUES ('$name','$email','$hash','$phone','$upi')
        ";

        if (mysqli_query($connect, $q)) {
            // ✅ redirect to combined login page
            header("Location: ../auth/login.php?registered=1");
            exit;
        } else {
            $msg = "Error: " . mysqli_error($connect);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Signup - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<h2>Host Signup</h2>

<p style="color:red;"><?php echo $msg; ?></p>

<form method="post">
    <input type="text" name="name" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="phone" placeholder="Phone" required><br><br>
    <input type="text" name="upi" placeholder="UPI ID (required)" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <button type="submit">Signup</button>
</form>

<p>
    Already a host?
    <a href="../auth/login.php">Login</a>
</p>
<?php include "../../includes/footer.php"; ?>

</body>
</html>
