<?php
session_start();
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $feedback = trim($_POST['message']);

    if ($name == "" || $email == "" || $feedback == "") {
        $message = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO feedback (name, email, subject, message)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("ssss", $name, $email, $subject, $feedback);

        if ($stmt->execute()) {
            $message = "Thank you! Your feedback submitted successfully.";
        } else {
            $message = "Something went wrong. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FitnessHealth | Feedback</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f6fb;
    color: #192a56;
}

header {
    background: #192a56;
    padding: 20px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h2 {
    color: white;
}

header span {
    color: #eb4d4b;
}

header a {
    color: white;
    text-decoration: none;
}

header a:hover {
    color: #eb4d4b;
}

.container {
    max-width: 650px;
    margin: 55px auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.container h1 {
    text-align: center;
    margin-bottom: 12px;
}

.container p {
    text-align: center;
    color: #777;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input, textarea {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

textarea {
    resize: vertical;
    min-height: 130px;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #eb4d4b;
}

button {
    width: 100%;
    padding: 14px;
    background: #eb4d4b;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    cursor: pointer;
}

button:hover {
    background: #c0392b;
}

.message {
    background: #e8f8ee;
    color: #187a39;
    padding: 12px;
    text-align: center;
    border-radius: 6px;
    margin-bottom: 20px;
}

footer {
    background: #192a56;
    color: white;
    text-align: center;
    padding: 20px;
    margin-top: 50px;
}

@media(max-width: 600px) {
    .container {
        margin: 25px 15px;
        padding: 25px;
    }
}

</style>
</head>

<body>

<header>

    <h2><span>Fitness</span>Health</h2>

    <a href="home.html">
        <i class="fa-solid fa-house"></i> Home
    </a>

</header>

<div class="container">

    <h1>Share Your Feedback</h1>

    <p>We value your feedback. Help us improve our services.</p>

    <?php if ($message != ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label>Your Name *</label>
            <input type="text" name="name"
                   placeholder="Enter your name" required>
        </div>

        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email"
                   placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject"
                   placeholder="Enter subject">
        </div>

        <div class="form-group">
            <label>Your Feedback *</label>
            <textarea name="message"
                      placeholder="Write your feedback here..."
                      required></textarea>
        </div>

        <button type="submit">
            <i class="fa-solid fa-paper-plane"></i>
            Submit Feedback
        </button>

    </form>

</div>

<footer>
    <p>© 2026 FitnessHealth. All Rights Reserved.</p>
</footer>

</body>
</html>