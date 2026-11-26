<?php
session_start();
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {

        // Check existing email
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "Email already registered!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users
                (firstname, lastname, email, password, gender, phone)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $firstname,
                $lastname,
                $email,
                $hashed_password,
                $gender,
                $phone
            );

            if ($stmt->execute()) {
                header("Location: login.php?registered=success");
                exit();
            } else {
                $message = "Registration failed. Please try again.";
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

    <title>FitnessHealth | Register</title>

    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f5f8;
        }

        .register-box {
            width: 380px;
            max-width: 90%;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .register-box h1 {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-box input,
        .register-box select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .register-box button {
            width: 100%;
            padding: 13px;
            background: #e63946;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .register-box button:hover {
            background: #c1121f;
        }

        .message {
            text-align: center;
            color: red;
            margin-bottom: 10px;
        }

        .login-link {
            text-align: center;
            margin-top: 18px;
        }
    </style>
</head>

<body>

<div class="register-box">

    <h1>Create Account</h1>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" action="">

        <input type="text" name="firstname"
               placeholder="First Name" required>

        <input type="text" name="lastname"
               placeholder="Last Name" required>

        <input type="email" name="email"
               placeholder="Email Address" required>

        <input type="text" name="phone"
               placeholder="Phone Number"
               pattern="[0-9]{10}"
               maxlength="10" required>

        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <input type="password" name="password"
               placeholder="Password"
               minlength="8" required>

        <input type="password" name="confirm_password"
               placeholder="Confirm Password"
               minlength="8" required>

        <button type="submit">Register Now</button>

    </form>

    <div class="login-link">
        Already have an account?
        <a href="login.php">Login</a>
    </div>

</div>

</body>
</html>
