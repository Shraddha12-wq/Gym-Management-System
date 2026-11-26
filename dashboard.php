<?php
session_start();
require_once "config.php";

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$username = htmlspecialchars($_SESSION['user_name']);
$email = htmlspecialchars($_SESSION['user_email']);

// Get latest membership details
$membership = null;

$stmt = $conn->prepare(
    "SELECT
        m.id AS membership_id,
        m.start_date,
        m.end_date,
        m.status AS membership_status,
        p.plan_name,
        p.price,
        pay.payment_method,
        pay.payment_status

    FROM memberships m

    INNER JOIN membership_plans p
        ON m.plan_id = p.id

    LEFT JOIN payments pay
        ON m.id = pay.membership_id

    WHERE m.user_id = ?

    ORDER BY m.id DESC

    LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$membership = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>FitnessHealth | Dashboard</title>

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

        .logout:hover {
            background: #c0392b;
        }

        /* Main Container */

        .container {
            padding: 40px 6%;
        }

        /* Welcome Section */

        .welcome {
            background: linear-gradient(120deg, #192a56, #30336b);
            padding: 35px;
            border-radius: 12px;
            border-left: 5px solid #eb4d4b;
        }

        .welcome h1 {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .welcome p {
            color: #ddd;
            line-height: 1.7;
            margin-top: 5px;
        }

        /* Section Title */

        .section-title {
            margin: 35px 0 20px;
            font-size: 25px;
        }

        /* Membership Section */

        .membership-box {
            background: #192a56;
            padding: 28px;
            border-radius: 12px;
            border-left: 5px solid #eb4d4b;
        }

        .membership-box h3 {
            font-size: 23px;
            margin-bottom: 20px;
            color: #fff;
        }

        .membership-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .detail {
            background: #30336b;
            padding: 18px;
            border-radius: 8px;
        }

        .detail p {
            color: #ccc;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .detail h4 {
            color: white;
            font-size: 17px;
        }

        .status {
            color: #f6e58d !important;
        }

        .no-membership {
            text-align: center;
            padding: 25px;
            background: #30336b;
            border-radius: 8px;
        }

        .no-membership p {
            color: #ddd;
            margin-bottom: 15px;
        }

        .membership-btn {
            display: inline-block;
            background: #eb4d4b;
            color: white;
            padding: 11px 22px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }

        .membership-btn:hover {
            background: #c0392b;
        }

        /* Cards */

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
        }

        .card {
            background: #192a56;
            padding: 28px;
            border-radius: 12px;
            text-align: center;
            border-bottom: 4px solid #eb4d4b;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-7px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .card i {
            font-size: 38px;
            color: #eb4d4b;
            margin-bottom: 18px;
        }

        .card h3 {
            margin-bottom: 12px;
        }

        .card p {
            color: #ccc;
            font-size: 14px;
            line-height: 1.6;
        }

        .card a {
            display: inline-block;
            margin-top: 18px;
            padding: 11px 20px;
            background: #eb4d4b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .card a:hover {
            background: #c0392b;
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
        Fitness<span>Health</span>
    </div>

    <a href="logout.php" class="logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</header>

<div class="container">

    <!-- Welcome Section -->

    <div class="welcome">

        <h1>Welcome, <?php echo $username; ?>!</h1>

        <p>
            Welcome to your FitnessHealth Member Dashboard.
        </p>

        <p>
            <i class="fa-solid fa-envelope"></i>
            <?php echo $email; ?>
        </p>

    </div>


    <!-- Membership Details -->

    <h2 class="section-title">
        <i class="fa-solid fa-id-card"></i>
        My Membership Details
    </h2>

    <div class="membership-box">

        <?php if ($membership): ?>

            <h3>
                <?php echo htmlspecialchars($membership['plan_name']); ?>
            </h3>

            <div class="membership-details">

                <div class="detail">

                    <p>Membership ID</p>

                    <h4>
                        #<?php echo (int)$membership['membership_id']; ?>
                    </h4>

                </div>

                <div class="detail">

                    <p>Membership Amount</p>

                    <h4>
                        ₹<?php echo number_format((float)$membership['price'], 2); ?>
                    </h4>

                </div>

                <div class="detail">

                    <p>Start Date</p>

                    <h4>
                        <?php echo htmlspecialchars($membership['start_date']); ?>
                    </h4>

                </div>

                <div class="detail">

                    <p>End Date</p>

                    <h4>
                        <?php echo htmlspecialchars($membership['end_date']); ?>
                    </h4>

                </div>

                <div class="detail">

                    <p>Membership Status</p>

                    <h4 class="status">
                        <?php echo htmlspecialchars($membership['membership_status']); ?>
                    </h4>

                </div>

                <div class="detail">

                    <p>Payment Method</p>

                    <h4>
                        <?php echo htmlspecialchars($membership['payment_method'] ?? 'Not Available'); ?>
                    </h4>

                </div>

                <div class="detail">

                    <p>Payment Status</p>

                    <h4 class="status">
                        <?php echo htmlspecialchars($membership['payment_status'] ?? 'Not Available'); ?>
                    </h4>

                </div>

            </div>

        <?php else: ?>

            <div class="no-membership">

                <h3>No Membership Found</h3>

                <p>
                    You have not purchased any membership plan yet.
                </p>

                <a href="membership.php" class="membership-btn">
                    Explore Membership Plans
                </a>

            </div>

        <?php endif; ?>

    </div>


    <!-- Dashboard Cards -->

    <h2 class="section-title">
        My Fitness Dashboard
    </h2>

    <div class="cards">

        <div class="card">

            <i class="fa-solid fa-dumbbell"></i>

            <h3>Membership Plans</h3>

            <p>
                Explore our available membership plans.
            </p>

            <a href="membership.php">
                View Plans
            </a>

        </div>


        <div class="card">

            <i class="fa-solid fa-calendar-days"></i>

            <h3>Workout Schedule</h3>

            <p>
                Check your weekly workout schedule.
            </p>

            <a href="schedule.html">
                View Schedule
            </a>

        </div>


        <div class="card">

            <i class="fa-solid fa-star"></i>

            <h3>Feedback</h3>

            <p>
                Share your experience with our gym.
            </p>

            <a href="Feedback.html">
                Give Feedback
            </a>

        </div>


        <div class="card">

            <i class="fa-solid fa-user"></i>

            <h3>My Profile</h3>

            <p>
                View your account information.
            </p>

            <a href="profile.php">
                My Profile
            </a>

        </div>

    </div>

</div>


<footer>

    <p>
        © 2026 FitnessHealth. All Rights Reserved.
    </p>

</footer>

</body>

</html>