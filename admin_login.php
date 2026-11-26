<?php
session_start();
require_once "config.php";

$message = "";

// If admin already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username == "" || $password == "") {

        $message = "Please enter username and password.";

    } else {

        // Check admin username
        $stmt = $conn->prepare(
            "SELECT id, username, password
             FROM admin
             WHERE username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $admin = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $admin['password'])) {

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                header("Location: admin_dashboard.php");
                exit();

            } else {

                $message = "Invalid username or password.";
            }

        } else {

            $message = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | FitnessHealth</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #130f40;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-box {
            background: #192a56;
            width: 100%;
            max-width: 420px;
            padding: 35px;
            border-radius: 12px;
            border-top: 4px solid #eb4d4b;
            color: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #ccc;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 9px;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 20px;
            border: none;
            border-radius: 6px;
            outline: none;
        }

        input:focus {
            border: 2px solid #eb4d4b;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #eb4d4b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #c0392b;
        }

        .message {
            color: #ffb8b8;
            text-align: center;
            margin-bottom: 18px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #ddd;
            text-decoration: none;
        }

        .back-link:hover {
            color: white;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h1>Admin Login</h1>

    <p class="subtitle">
        FitnessHealth Administration Panel
    </p>

    <?php if ($message): ?>

        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Admin Username</label>

        <input
            type="text"
            name="username"
            placeholder="Enter username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >

        <button type="submit">
            Login as Admin
        </button>

    </form>

    <a href="login.php" class="back-link">
        Back to User Login
    </a>

</div>

</body>
</html>