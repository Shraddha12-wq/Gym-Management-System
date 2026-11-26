<?php
session_start();
require_once "config.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (
        $firstname == "" ||
        $lastname == "" ||
        $phone == "" ||
        $gender == ""
    ) {
        $message = "Please fill all required fields.";
        $message_type = "error";

    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {

        $message = "Please enter a valid 10-digit phone number.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "UPDATE users
             SET firstname = ?,
                 lastname = ?,
                 phone = ?,
                 gender = ?,
                 address = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $firstname,
            $lastname,
            $phone,
            $gender,
            $address,
            $user_id
        );

        if ($stmt->execute()) {

            $message = "Profile updated successfully!";
            $message_type = "success";

            $_SESSION['user_name'] = $firstname . " " . $lastname;

        } else {

            $message = "Unable to update profile. Please try again.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

// Get user details
$stmt = $conn->prepare(
    "SELECT firstname, lastname, email, phone, gender, address
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    die("User information not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FitnessHealth | My Profile</title>

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
    background: #130f40;
    color: white;
}

/* Header */

header {
    background: #192a56;
    padding: 20px 6%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #eb4d4b;
}

.logo {
    font-size: 25px;
    font-weight: bold;
}

.logo span {
    color: #eb4d4b;
}

header a {
    text-decoration: none;
    color: white;
    background: #eb4d4b;
    padding: 11px 20px;
    border-radius: 5px;
}

/* Main */

.container {
    max-width: 750px;
    margin: 40px auto;
    padding: 20px;
}

.profile-box {
    background: #192a56;
    padding: 35px;
    border-radius: 12px;
    border-top: 4px solid #eb4d4b;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

/* Profile Icon */

.profile-icon {
    text-align: center;
    margin-bottom: 25px;
}

.profile-icon i {
    font-size: 75px;
    color: #eb4d4b;
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

/* Form */

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 9px;
    font-weight: bold;
}

input, select, textarea {
    width: 100%;
    padding: 13px;
    border: 1px solid #555;
    border-radius: 6px;
    outline: none;
    font-size: 15px;
    background: #30336b;
    color: white;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #eb4d4b;
}

input[readonly] {
    background: #252c50;
    color: #bbb;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

/* Button */

button {
    width: 100%;
    padding: 14px;
    background: #eb4d4b;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
}

button:hover {
    background: #c0392b;
}

/* Messages */

.message {
    padding: 13px;
    border-radius: 6px;
    text-align: center;
    margin-bottom: 20px;
}

.success {
    background: #d4edda;
    color: #155724;
}

.error {
    background: #f8d7da;
    color: #721c24;
}

/* Footer */

footer {
    text-align: center;
    padding: 25px;
    background: #192a56;
    color: #ccc;
    margin-top: 40px;
    border-top: 2px solid #eb4d4b;
}

/* Mobile */

@media(max-width: 600px) {

    header {
        padding: 18px;
    }

    .container {
        margin: 20px auto;
    }

    .profile-box {
        padding: 22px;
    }

    .row {
        grid-template-columns: 1fr;
        gap: 0;
    }

}

</style>

</head>

<body>

<header>

    <div class="logo">
        Fitness<span>Health</span>
    </div>

    <a href="dashboard.php">
        <i class="fa-solid fa-arrow-left"></i>
        Dashboard
    </a>

</header>


<div class="container">

    <div class="profile-box">

        <div class="profile-icon">
            <i class="fa-solid fa-circle-user"></i>
        </div>

        <h1>My Profile</h1>

        <p class="subtitle">
            View and update your personal information.
        </p>

        <?php if ($message): ?>

            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <div class="row">

                <div class="form-group">

                    <label>First Name</label>

                    <input
                        type="text"
                        name="firstname"
                        value="<?= htmlspecialchars($user['firstname'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Last Name</label>

                    <input
                        type="text"
                        name="lastname"
                        value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>Email Address</label>

                <input
                    type="email"
                    value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                    readonly
                >

            </div>


            <div class="form-group">

                <label>Phone Number</label>

                <input
                    type="tel"
                    name="phone"
                    maxlength="10"
                    pattern="[0-9]{10}"
                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Gender</label>

                <select name="gender" required>

                    <option value="">Select Gender</option>

                    <option value="Male"
                        <?= ($user['gender'] == 'Male') ? 'selected' : '' ?>>
                        Male
                    </option>

                    <option value="Female"
                        <?= ($user['gender'] == 'Female') ? 'selected' : '' ?>>
                        Female
                    </option>

                    <option value="Other"
                        <?= ($user['gender'] == 'Other') ? 'selected' : '' ?>>
                        Other
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>Address</label>

                <textarea
                    name="address"
                    placeholder="Enter your address"
                ><?= htmlspecialchars($user['address'] ?? '') ?></textarea>

            </div>


            <button type="submit">

                <i class="fa-solid fa-floppy-disk"></i>
                Update Profile

            </button>

        </form>

    </div>

</div>


<footer>

    <p>© 2026 FitnessHealth. All Rights Reserved.</p>

</footer>

</body>

</html>