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

// Flash Message
if (isset($_SESSION['payment_message'])) {
    $message = $_SESSION['payment_message'];
    unset($_SESSION['payment_message']);
}

// Update Payment Status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_payment'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid CSRF Token.");
    }

    $payment_id = (int) $_POST['payment_id'];
    $payment_status = $_POST['payment_status'];

    if ($payment_id <= 0 || !in_array($payment_status, ['Pending', 'Paid', 'Failed'], true)) {
        $_SESSION['payment_message'] = "Invalid payment details.";
        header("Location: manage_payments.php");
        exit();
    }

    try {
        $conn->begin_transaction();

        // Get membership ID
        $stmt = $conn->prepare(
            "SELECT membership_id FROM payments WHERE id = ?"
        );
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$payment) {
            throw new Exception("Payment not found.");
        }

        // Update Payment Status
        $stmt = $conn->prepare(
            "UPDATE payments SET payment_status = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $payment_status, $payment_id);
        $stmt->execute();
        $stmt->close();

        // Update Membership Status
        if (!empty($payment['membership_id'])) {

            $membership_status = ($payment_status === 'Paid')
                ? 'Active'
                : 'Pending';

            $membership_id = (int) $payment['membership_id'];

            $stmt = $conn->prepare(
                "UPDATE memberships SET status = ? WHERE id = ?"
            );
            $stmt->bind_param("si", $membership_status, $membership_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        $_SESSION['payment_message'] = "Payment status updated successfully!";

    } catch (Exception $e) {

        $conn->rollback();
        $_SESSION['payment_message'] = "Error: " . $e->getMessage();
    }

    header("Location: manage_payments.php");
    exit();
}

// Fetch Payments
$sql = "
    SELECT
        p.id AS payment_id,
        p.user_id,
        p.membership_id,
        p.amount,
        p.payment_method,
        p.payment_status,
        p.payment_date,
        u.firstname,
        u.lastname,
        u.email,
        mp.plan_name
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN memberships m ON p.membership_id = m.id
    LEFT JOIN membership_plans mp ON m.plan_id = mp.id
    ORDER BY p.payment_date DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Payment query failed: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Payments | FitnessHealth</title>

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
        }

        .card-header {
            background: #101d3a;
            color: white;
            padding: 15px;
            font-weight: bold;
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

    <div class="header d-flex justify-content-between align-items-center">
        <h2>Manage Payments</h2>

        <a href="admin_dashboard.php" class="back-btn">
            Back to Dashboard
        </a>
    </div>

    <div class="container">

        <?php if ($message !== ""): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">

            <div class="card-header">
                All Payment Records
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover text-center align-middle">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Payment Date</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($result->num_rows > 0): ?>

                                <?php while ($row = $result->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <?php echo (int) $row['payment_id']; ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''))
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['email'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['plan_name'] ?? 'N/A'); ?>
                                        </td>

                                        <td>
                                            ₹<?php echo number_format((float) $row['amount'], 2); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['payment_date']); ?>
                                        </td>

                                        <td>
                                            <?php if ($row['payment_status'] === 'Paid'): ?>

                                                <span class="badge bg-success">Paid</span>

                                            <?php elseif ($row['payment_status'] === 'Failed'): ?>

                                                <span class="badge bg-danger">Failed</span>

                                            <?php else: ?>

                                                <span class="badge bg-warning text-dark">Pending</span>

                                            <?php endif; ?>
                                        </td>

                                        <td>

                                            <form method="POST" action="">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                                <input type="hidden"
                                                       name="payment_id"
                                                       value="<?php echo (int) $row['payment_id']; ?>">

                                                <select name="payment_status"
                                                        class="form-select form-select-sm mb-2"
                                                        required>

                                                    <option value="Pending"
                                                        <?php echo $row['payment_status'] === 'Pending' ? 'selected' : ''; ?>>
                                                        Pending
                                                    </option>

                                                    <option value="Paid"
                                                        <?php echo $row['payment_status'] === 'Paid' ? 'selected' : ''; ?>>
                                                        Paid
                                                    </option>

                                                    <option value="Failed"
                                                        <?php echo $row['payment_status'] === 'Failed' ? 'selected' : ''; ?>>
                                                        Failed
                                                    </option>

                                                </select>

                                                <button type="submit"
                                                        name="update_payment"
                                                        class="btn btn-sm btn-primary">
                                                    Update
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="9">
                                        No payment records found.
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
