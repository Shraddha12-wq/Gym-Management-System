<?php
session_start();
require_once "config.php";

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$message = "";
$plan = null;

// Check success page
$is_success_page = (
    isset($_GET['success']) &&
    $_GET['success'] == '1'
);

// Get selected plan ID
$plan_id = isset($_POST['plan_id'])
    ? (int) $_POST['plan_id']
    : (int) ($_GET['plan_id'] ?? 0);

// Fetch membership plan
if (!$is_success_page) {

    $stmt = $conn->prepare(
        "SELECT * FROM membership_plans
         WHERE id = ? AND status = 'Active'"
    );

    $stmt->bind_param("i", $plan_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();

    $stmt->close();

    if (!$plan) {
        die("Invalid membership plan. Please select a valid plan.");
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process membership purchase
if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    !$is_success_page
) {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $payment_method = $_POST['payment_method'] ?? '';

    if ($payment_method !== 'Cash') {

        $message = "Please select a valid payment method.";

    } else {

        $start_date = date("Y-m-d");

        $end = new DateTime($start_date);

        $end->modify(
            "+" . (int) $plan['duration_months'] . " months"
        );

        $end_date = $end->format("Y-m-d");

        $amount = (float) $plan['price'];

        try {

            $conn->begin_transaction();

            // Insert membership
            $membership = $conn->prepare(
                "INSERT INTO memberships
                (user_id, plan_id, start_date, end_date, status)
                VALUES (?, ?, ?, ?, 'Pending')"
            );

            $membership->bind_param(
                "iiss",
                $user_id,
                $plan_id,
                $start_date,
                $end_date
            );

            $membership->execute();

            $membership_id = $conn->insert_id;

            $membership->close();

            // Insert payment
            $payment = $conn->prepare(
                "INSERT INTO payments
                (user_id, membership_id, amount,
                 payment_method, payment_status)
                VALUES (?, ?, ?, ?, 'Pending')"
            );

            $payment->bind_param(
                "iids",
                $user_id,
                $membership_id,
                $amount,
                $payment_method
            );

            $payment->execute();

            $payment->close();

            // Commit transaction
            $conn->commit();

            // Save success details in session
            $_SESSION['purchase_success'] = [
                'plan' => $plan['plan_name'],
                'amount' => $amount,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'membership_id' => $membership_id
            ];

            // Remove old CSRF token
            unset($_SESSION['csrf_token']);

            // Redirect to success page
            header("Location: purchase.php?success=1");
            exit();

        } catch (Throwable $e) {

            $conn->rollback();

            error_log($e->getMessage());

            $message = "Purchase failed. Please try again.";
        }
    }
}

// Get success details
$success = null;

if ($is_success_page) {

    $success = $_SESSION['purchase_success'] ?? null;

    if (!$success) {
        header("Location: membership.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>FitnessHealth | Purchase Membership</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f5f8;
            color: #333;
        }

        header {
            background: #192a56;
            color: white;
            padding: 20px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h2 {
            margin: 0;
        }

        header a {
            color: white;
            text-decoration: none;
            background: #e63946;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .container {
            max-width: 550px;
            margin: 45px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #192a56;
        }

        .plan-details {
            background: #f3f5f8;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .price {
            font-size: 30px;
            color: #e63946;
            font-weight: bold;
        }

        label {
            display: block;
            margin: 15px 0 8px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 20px;
            background: #e63946;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #c1121f;
        }

        .message {
            color: red;
            text-align: center;
        }

        .success {
            color: #198754;
            text-align: center;
            line-height: 2;
        }

        .success h2 {
            color: #192a56;
        }

        .success p {
            color: #333;
        }

        .success-box {
            background: #f0fff4;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .dashboard-btn {
            display: block;
            text-align: center;
            text-decoration: none;
            background: #192a56;
            color: white;
            padding: 13px;
            border-radius: 6px;
            margin-top: 20px;
        }

    </style>

</head>

<body>

<header>

    <h2>FitnessHealth</h2>

    <a href="dashboard.php">Dashboard</a>

</header>

<div class="container">

    <?php if ($success): ?>

        <!-- Purchase Success Page -->

        <h1>Purchase Submitted!</h1>

        <div class="success-box">

            <div class="success">

                <h2>Membership Details</h2>

                <p>
                    <strong>Plan:</strong>
                    <?= htmlspecialchars($success['plan']) ?>
                </p>

                <p>
                    <strong>Amount:</strong>
                    ₹<?= number_format($success['amount'], 2) ?>
                </p>

                <p>
                    <strong>Start Date:</strong>
                    <?= htmlspecialchars($success['start_date']) ?>
                </p>

                <p>
                    <strong>End Date:</strong>
                    <?= htmlspecialchars($success['end_date']) ?>
                </p>

                <p>
                    <strong>Membership ID:</strong>
                    <?= (int) $success['membership_id'] ?>
                </p>

                <h3>Payment Status: Pending</h3>

                <p>Please pay at the gym reception.</p>

            </div>

        </div>

        <a href="dashboard.php" class="dashboard-btn">
            Go to Dashboard
        </a>

    <?php else: ?>

        <!-- Purchase Form -->

        <h1>Purchase Membership</h1>

        <?php if ($message): ?>

            <p class="message">
                <?= htmlspecialchars($message) ?>
            </p>

        <?php endif; ?>

        <div class="plan-details">

            <h2>
                <?= htmlspecialchars($plan['plan_name']) ?>
            </h2>

            <p class="price">
                ₹<?= number_format($plan['price'], 2) ?>
            </p>

            <p>
                <strong>Duration:</strong>
                <?= (int) $plan['duration_months'] ?> Months
            </p>

            <p>
                <?= htmlspecialchars($plan['description']) ?>
            </p>

        </div>

        <form method="POST">

            <input
                type="hidden"
                name="plan_id"
                value="<?= (int) $plan['id'] ?>"
            >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label>Payment Method</label>

            <select name="payment_method" required>

                <option value="">
                    Select Payment Method
                </option>

                <option value="Cash">
                    Cash at Gym Reception
                </option>

            </select>

            <button type="submit">
                Confirm Membership
            </button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>