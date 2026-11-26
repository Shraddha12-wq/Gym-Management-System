<?php
session_start();
require_once "config.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die("Invalid membership plan ID.");
}

// Fetch plan
$stmt = $conn->prepare(
    "SELECT * FROM membership_plans WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$plan) {
    die("Membership plan not found.");
}

// Update plan
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid request. Please refresh the page.");
    }

    $plan_name = trim($_POST['plan_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration = (int)($_POST['duration_months'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? '';

    if (
        $plan_name === '' ||
        $price <= 0 ||
        $duration <= 0 ||
        !in_array($status, ['Active', 'Inactive'], true)
    ) {
        $message = "Please enter valid plan details.";
    } else {

        $stmt = $conn->prepare(
            "UPDATE membership_plans
             SET plan_name = ?,
                 price = ?,
                 duration_months = ?,
                 description = ?,
                 status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sdissi",
            $plan_name,
            $price,
            $duration,
            $description,
            $status,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();

            header("Location: manage_plans.php?updated=1");
            exit();
        } else {
            $message = "Unable to update membership plan.";
            $stmt->close();
        }
    }

    // Keep submitted values in the form
    $plan['plan_name'] = $plan_name;
    $plan['price'] = $price;
    $plan['duration_months'] = $duration;
    $plan['description'] = $description;
    $plan['status'] = $status;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Membership Plan</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #130f40;
            color: white;
            margin: 0;
        }

        .container {
            max-width: 550px;
            margin: 50px auto;
            background: #192a56;
            padding: 30px;
            border-radius: 12px;
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 15px 0 8px;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 20px;
            background: #eb4d4b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back {
            display: block;
            text-align: center;
            color: white;
            margin-top: 20px;
            text-decoration: none;
        }

        .message {
            color: #f6e58d;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Edit Membership Plan</h1>

    <?php if ($message): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="id" value="<?= $id ?>">

        <input type="hidden"
               name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Plan Name</label>
        <input type="text"
               name="plan_name"
               value="<?= htmlspecialchars($plan['plan_name']) ?>"
               required>

        <label>Price (₹)</label>
        <input type="number"
               name="price"
               min="1"
               step="0.01"
               value="<?= htmlspecialchars($plan['price']) ?>"
               required>

        <label>Duration (Months)</label>
        <input type="number"
               name="duration_months"
               min="1"
               value="<?= (int)$plan['duration_months'] ?>"
               required>

        <label>Description</label>
        <textarea name="description"
                  rows="4"><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>

        <label>Status</label>
        <select name="status" required>
            <option value="Active"
                <?= $plan['status'] === 'Active' ? 'selected' : '' ?>>
                Active
            </option>

            <option value="Inactive"
                <?= $plan['status'] === 'Inactive' ? 'selected' : '' ?>>
                Inactive
            </option>
        </select>

        <button type="submit">Update Membership Plan</button>

    </form>

    <a href="manage_plans.php" class="back">
        Back to Manage Plans
    </a>

</div>

</body>
</html>