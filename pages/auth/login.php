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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zevent</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <style>
        /* Ensure select options are visible */
        select option {
            color: #000000;
            background: #ffffff;
        }
        
        /* Error message styling */
        .error-message {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            font-size: 14px;
        }
        
        /* Links styling */
        .auth-links {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
            color: #94a3b8;
        }
        
        .auth-links a {
            color: #818cf8;
            font-weight: 500;
            text-decoration: none;
        }
        
        .auth-links a:hover {
            color: #a5b4fc;
            text-decoration: underline;
        }
        
        /* Logo at top */
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .login-logo p {
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
</head>

<body class="auth-page">

<div class="container">
    <div class="login-form">
        
        <!-- Logo -->
        <div class="login-logo">
            <h1>⚡ Zevent</h1>
            <p>Login to your account</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error-message">
                <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="">
            
            <select name="role" required>
                <option value="">Login As</option>
                <option value="user">User</option>
                <option value="host">Host</option>
            </select>
            
            <input 
                type="email" 
                name="email" 
                placeholder="Email Address" 
                required
                autocomplete="email"
            >
            
            <input 
                type="password" 
                name="password" 
                placeholder="Password" 
                required
                autocomplete="current-password"
            >
            
            <button type="submit">Login</button>
            
        </form>
        
        <!-- Sign Up Links -->
        <div class="auth-links">
            <p>
                New User? <a href="../user/user_signup.php">Sign up</a>
            </p>
            <p style="margin-top: 5px;">
                New Host? <a href="../host/host_signup.php">Become a Host</a>
            </p>
        </div>
        
    </div>
</div>

<?php include "../../includes/footer.php"; ?>

</body>
</html>