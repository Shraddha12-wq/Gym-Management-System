<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query(
    "SELECT * FROM membership_plans WHERE status='Active' ORDER BY price ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FitnessHealth | Membership</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f3f5f8;
}

header {
    background: #172b4d;
    color: white;
    padding: 20px 6%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header a {
    color: white;
    text-decoration: none;
    margin-left: 15px;
}

.container {
    padding: 50px 6%;
    text-align: center;
}

.container h1 {
    font-size: 36px;
}

.plans {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 40px;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.price {
    font-size: 32px;
    color: #e63946;
    font-weight: bold;
}

.card button {
    background: #e63946;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.card button:hover {
    background: #c1121f;
}
</style>
</head>

<body>

<header>
    <h2>FitnessHealth</h2>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">

    <h1>Our Membership Plans</h1>
    <p>Choose the right fitness plan for your goals.</p>

    <div class="plans">

        <?php while ($plan = $result->fetch_assoc()) { ?>

        <div class="card">

            <h2>
                <?php echo htmlspecialchars($plan['plan_name']); ?>
            </h2>

            <div class="price">
                ₹<?php echo number_format($plan['price']); ?>
            </div>

            <p>
                Duration:
                <?php echo (int)$plan['duration_months']; ?> Months
            </p>

            <p>
                <?php echo htmlspecialchars($plan['description']); ?>
            </p>

            <form action="purchase.php" method="GET">
                <input type="hidden"
                       name="plan_id"
                       value="<?php echo (int)$plan['id']; ?>">

                <button type="submit">Choose Plan</button>
            </form>

        </div>

        <?php } ?>

    </div>

</div>

</body>
</html>
