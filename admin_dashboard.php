<?php
session_start();
require_once "config.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = htmlspecialchars($_SESSION['admin_username']);

// Total Users
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_users = $result->fetch_assoc()['total'];

// Total Memberships
$result = $conn->query("SELECT COUNT(*) AS total FROM memberships");
$total_memberships = $result->fetch_assoc()['total'];

// Pending Payments
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM payments
     WHERE payment_status = 'Pending'"
);
$pending_payments = $result->fetch_assoc()['total'];

// Total Revenue (Paid Payments Only)
$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS revenue
     FROM payments
     WHERE payment_status = 'Paid'"
);
$total_revenue = $result->fetch_assoc()['revenue'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard | FitnessHealth</title>

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

.logout {
    background: #eb4d4b;
    color: white;
    padding: 11px 22px;
    border-radius: 5px;
    text-decoration: none;
}

/* Container */

.container {
    padding: 40px 6%;
}

/* Welcome */

.welcome {
    background: linear-gradient(120deg, #192a56, #30336b);
    padding: 35px;
    border-radius: 12px;
    border-left: 5px solid #eb4d4b;
}

.welcome h1 {
    margin-bottom: 12px;
    font-size: 30px;
}

.welcome p {
    color: #ddd;
    line-height: 1.7;
}

/* Statistics */

.section-title {
    margin: 35px 0 20px;
    font-size: 25px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 22px;
}

.stat-card {
    background: #192a56;
    padding: 28px;
    border-radius: 12px;
    border-bottom: 4px solid #eb4d4b;
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-6px);
}

.stat-card i {
    font-size: 35px;
    color: #eb4d4b;
    margin-bottom: 18px;
}

.stat-card h3 {
    color: #ccc;
    font-size: 16px;
    margin-bottom: 12px;
}

.stat-card h2 {
    font-size: 28px;
}

/* Management Cards */

.management {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 22px;
}

.manage-card {
    background: #192a56;
    padding: 28px;
    text-align: center;
    border-radius: 12px;
    border-bottom: 4px solid #eb4d4b;
    transition: 0.3s;
}

.manage-card:hover {
    transform: translateY(-6px);
}

.manage-card i {
    font-size: 38px;
    color: #eb4d4b;
    margin-bottom: 18px;
}

.manage-card h3 {
    margin-bottom: 12px;
}

.manage-card p {
    color: #ccc;
    font-size: 14px;
    line-height: 1.6;
}

.manage-card a {
    display: inline-block;
    margin-top: 18px;
    padding: 11px 20px;
    background: #eb4d4b;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

/* Footer */

footer {
    text-align: center;
    padding: 25px;
    margin-top: 50px;
    background: #192a56;
    color: #ccc;
    border-top: 2px solid #eb4d4b;
}

/* Mobile */

@media(max-width: 600px) {

    header {
        padding: 18px;
    }

    .container {
        padding: 25px 18px;
    }

    .welcome h1 {
        font-size: 23px;
    }

    .section-title {
        font-size: 22px;
    }

}

</style>

</head>

<body>

<header>

    <div class="logo">
        Fitness<span>Health</span> Admin
    </div>

    <a href="admin_logout.php" class="logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</header>


<div class="container">

    <!-- Welcome -->

    <div class="welcome">

        <h1>
            Welcome, <?php echo $admin_name; ?>!
        </h1>

        <p>
            Welcome to FitnessHealth Administration Dashboard.
        </p>

        <p>
            Manage your gym members, memberships, payments and feedback.
        </p>

    </div>


    <!-- Statistics -->

    <h2 class="section-title">
        <i class="fa-solid fa-chart-line"></i>
        Dashboard Statistics
    </h2>

    <div class="stats">

        <div class="stat-card">

            <i class="fa-solid fa-users"></i>

            <h3>Total Registered Users</h3>

            <h2><?php echo $total_users; ?></h2>

        </div>


        <div class="stat-card">

            <i class="fa-solid fa-id-card"></i>

            <h3>Total Memberships</h3>

            <h2><?php echo $total_memberships; ?></h2>

        </div>


        <div class="stat-card">

            <i class="fa-solid fa-clock"></i>

            <h3>Pending Payments</h3>

            <h2><?php echo $pending_payments; ?></h2>

        </div>


        <div class="stat-card">

            <i class="fa-solid fa-indian-rupee-sign"></i>

            <h3>Total Revenue</h3>

            <h2>
                ₹<?php echo number_format((float)$total_revenue, 2); ?>
            </h2>

        </div>

    </div>


    <!-- Management -->

    <h2 class="section-title">
        <i class="fa-solid fa-gear"></i>
        Manage Gym
    </h2>

    <div class="management">

        <div class="manage-card">

            <i class="fa-solid fa-users"></i>

            <h3>Manage Members</h3>

            <p>View and manage registered gym members.</p>

            <a href="manage_members.php">
                Manage Members
            </a>

        </div>


        <div class="manage-card">

            <i class="fa-solid fa-dumbbell"></i>

            <h3>Membership Plans</h3>

            <p>Add, update and delete membership plans.</p>

            <a href="manage_plans.php">
                Manage Plans
            </a>

        </div>


        <div class="manage-card">

            <i class="fa-solid fa-money-bill-wave"></i>

            <h3>Manage Payments</h3>

            <p>Check pending and completed payments.</p>

            <a href="manage_payments.php">
                Manage Payments
            </a>

        </div>


        <div class="manage-card">

            <i class="fa-solid fa-comments"></i>

            <h3>Manage Feedback</h3>

            <p>View feedback submitted by gym members.</p>

            <a href="manage_feedback.php">
                View Feedback
            </a>

        </div>

    </div>

</div>


<footer>

    <p>
        © 2026 FitnessHealth Admin Panel. All Rights Reserved.
    </p>

</footer>

</body>

</html>