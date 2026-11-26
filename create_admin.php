<?php
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($username == "" || $password == "") {

        $message = "Please fill all fields.";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        // Check username
        $check = $conn->prepare(
            "SELECT id FROM admin WHERE username = ?"
        );

        $check->bind_param("s", $username);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Admin username already exists.";

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert admin
            $stmt = $conn->prepare(
                "INSERT INTO admin (username, password)
                 VALUES (?, ?)"
            );

            $stmt->bind_param(
                "ss",
                $username,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Admin account created successfully!";

            } else {

                $message = "Unable to create admin account.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Admin | FitnessHealth</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #130f40;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: #192a56;
            padding: 35px;
            width: 90%;
            max-width: 430px;
            border-radius: 12px;
            color: white;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 15px 0 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 22px;
            background: #eb4d4b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .message {
            text-align: center;
            color: #f6e58d;
            margin-bottom: 15px;
        }
    </style>

</head>

<body>

<div class="container">

    <h2>Create Admin Account</h2>

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
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            minlength="8"
            required
        >

        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm_password"
            minlength="8"
            required
        >

        <button type="submit">
            Create Admin
        </button>

    </form>

</div>

</body>
</html>