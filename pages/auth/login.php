<?php
session_start();
include "../../includes/db_connect.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role  = $_POST['role'] ?? '';
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $pass  = $_POST['password'];

    if ($role === "user") {

        $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $res = mysqli_query($connect, $sql);

        if ($res && mysqli_num_rows($res) === 1) {
            $u = mysqli_fetch_assoc($res);

            if (password_verify($pass, $u['password'])) {
                $_SESSION['user_id']   = $u['user_id'];
                $_SESSION['user_name'] = $u['name'];
                header("Location: ../../index.php");
                exit;
            }
        }

        $error = "Invalid User credentials.";

    } elseif ($role === "host") {

        $sql = "SELECT * FROM hosts WHERE email='$email' LIMIT 1";
        $res = mysqli_query($connect, $sql);

        if ($res && mysqli_num_rows($res) === 1) {
            $h = mysqli_fetch_assoc($res);

            if (password_verify($pass, $h['password'])) {
                $_SESSION['host_id']   = $h['host_id'];
                $_SESSION['host_name'] = $h['name'];
                header("Location: ../host/host_dashboard.php");
                exit;
            }
        }

        $error = "Invalid Host credentials.";

    } else {
        $error = "Please select login type.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
   <style>
    body {
        padding-top: 120px;
        background: #0f172a;
    }

    .box {
        width: 420px;
        margin: auto;
        background: rgba(255, 255, 255, 0.08);
        padding: 30px;
        border-radius: 14px;
        color: #ffffff;
    }

    input,
    select,
    button {
        width: 100%;
        padding: 12px;
        margin-top: 12px;
        border-radius: 8px;
        border: none;
        font-size: 15px;
    }

    input,
    select {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }

    /* 🔑 FIX FOR DROPDOWN TEXT VISIBILITY */
    select option {
        color: #000000;
        background: #ffffff;
    }

    button {
        background: #6366f1;
        color: #ffffff;
        font-size: 16px;
        cursor: pointer;
    }
</style>

</head>

<body>

<div class="box">
    <h2 style="text-align:center;">Login to Zevent</h2>

    <?php if ($error): ?>
        <p style="color:#ff6b6b;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">

        <select name="role" required>
            <option value="">Login As</option>
            <option value="user">User</option>
            <option value="host">Host</option>
        </select>

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>

    </form>

    <p style="margin-top:15px; font-size:14px;">
        New User? <a href="../user/user_signup.php">Sign up</a><br>
        New Host? <a href="../host/host_signup.php">Become a Host</a>
    </p>
</div>
<?php include "../../includes/footer.php"; ?>

</body>
</html>
