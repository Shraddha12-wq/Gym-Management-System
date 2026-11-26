<?php
session_start();
require_once "config.php";

// Admin Login Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

// Show Flash Message
if (isset($_SESSION['plan_message'])) {
    $message = $_SESSION['plan_message'];
    unset($_SESSION['plan_message']);
}

// Add Membership Plan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_plan'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid CSRF Token.");
    }

    $plan_name = trim($_POST['plan_name']);
    $price = (float) $_POST['price'];
    $duration = (int) $_POST['duration_months'];
    $description = trim($_POST['description']);

    if ($plan_name === "" || $price <= 0 || $duration <= 0) {
        $message = "Please enter valid plan details.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO membership_plans
            (plan_name, price, duration_months, description, status)
            VALUES (?, ?, ?, ?, 'Active')"
        );

        $stmt->bind_param(
            "sdis",
            $plan_name,
            $price,
            $duration,
            $description
        );

        if ($stmt->execute()) {
            $_SESSION['plan_message'] = "Membership plan added successfully!";
            header("Location: manage_plans.php");
            exit();
        } else {
            $message = "Error adding membership plan.";
        }

        $stmt->close();
    }
}

// Fetch Membership Plans
$result = $conn->query(
    "SELECT * FROM membership_plans ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Membership Plans | FitnessHealth</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .header {
            background: #101d3a;
            color: white;
            padding: 20px 30px;
        }

        .header h2 {
            margin: 0;
        }

        .container {
            margin-top: 30px;
            margin-bottom: 40px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .card-header {
            background: #101d3a;
            color: white;
            padding: 15px;
            font-weight: bold;
        }

        .btn-primary {
            background: #101d3a;
            border: none;
        }

        .btn-primary:hover {
            background: #263e70;
        }

        .btn-edit {
            background: #ffc107;
            color: black;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        table th {
            background: #101d3a !important;
            color: white !important;
            white-space: nowrap;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            border: 1px solid white;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .back-btn:hover {
            background: white;
            color: #101d3a;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header d-flex justify-content-between align-items-center">
        <h2>Manage Membership Plans</h2>

        <a href="admin_dashboard.php" class="back-btn">
            Back to Dashboard
        </a>
    </div>

    <div class="container">

        <!-- Message -->
        <?php if ($message != ""): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>


        <!-- Add Membership Plan -->
        <div class="card">

            <div class="card-header">
                Add New Membership Plan
            </div>

            <div class="card-body">

                <form method="POST" action="">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plan Name</label>

                            <input type="text"
                                   name="plan_name"
                                   class="form-control"
                                   placeholder="Enter plan name"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (₹)</label>

                            <input type="number"
                                   name="price"
                                   class="form-control"
                                   min="1"
                                   step="0.01"
                                   placeholder="Enter price"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (Months)</label>

                            <input type="number"
                                   name="duration_months"
                                   class="form-control"
                                   min="1"
                                   placeholder="Enter duration"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Enter plan description"></textarea>
                        </div>

                    </div>

                    <button type="submit"
                            name="add_plan"
                            class="btn btn-primary">
                        Add Plan
                    </button>

                </form>

            </div>
        </div>


        <!-- Membership Plans List -->
        <div class="card">

            <div class="card-header">
                All Membership Plans
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover text-center align-middle">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plan Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($result && $result->num_rows > 0): ?>

                                <?php while ($row = $result->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <?php echo (int) $row['id']; ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['plan_name']); ?>
                                        </td>

                                        <td>
                                            ₹<?php echo number_format((float) $row['price'], 2); ?>
                                        </td>

                                        <td>
                                            <?php echo (int) $row['duration_months']; ?> Months
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['description'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?php if ($row['status'] === 'Active'): ?>

                                                <span class="badge bg-success">
                                                    Active
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    Inactive
                                                </span>

                                            <?php endif; ?>
                                        </td>

                                        <td>

                                            <!-- Edit Button -->
                                            <a href="edit_plan.php?id=<?php echo (int) $row['id']; ?>"
                                               class="btn btn-sm btn-edit mb-1">
                                                Edit
                                            </a>

                                            <!-- Delete Button -->
                                            <form method="POST"
                                                  action="delete_plan.php"
                                                  style="display:inline;">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?php echo (int) $row['id']; ?>">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                                <button type="submit"
                                                        class="btn btn-sm btn-delete mb-1"
                                                        onclick="return confirm('Are you sure you want to delete this plan?');">
                                                    Delete
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="7">
                                        No membership plans found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>

</body>
</html>