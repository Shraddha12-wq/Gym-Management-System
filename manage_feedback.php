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
if (isset($_SESSION['feedback_message'])) {
    $message = $_SESSION['feedback_message'];
    unset($_SESSION['feedback_message']);
}

// Delete Feedback
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_feedback'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid CSRF Token.");
    }

    $feedback_id = (int) $_POST['feedback_id'];

    if ($feedback_id <= 0) {
        $_SESSION['feedback_message'] = "Invalid feedback ID.";
    } else {

        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->bind_param("i", $feedback_id);

        if ($stmt->execute()) {
            $_SESSION['feedback_message'] = "Feedback deleted successfully!";
        } else {
            $_SESSION['feedback_message'] = "Unable to delete feedback.";
        }

        $stmt->close();
    }

    header("Location: manage_feedback.php");
    exit();
}

// Fetch Feedback
$result = $conn->query(
    "SELECT * FROM feedback ORDER BY created_at DESC"
);

if (!$result) {
    die("Feedback query failed: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Feedback | FitnessHealth</title>

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

        .message-cell {
            min-width: 250px;
            max-width: 400px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            text-align: left;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header d-flex justify-content-between align-items-center">
        <h2>Manage Feedback</h2>

        <a href="admin_dashboard.php" class="back-btn">
            Back to Dashboard
        </a>
    </div>

    <div class="container">

        <!-- Message -->
        <?php if ($message !== ""): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Feedback Table -->
        <div class="card">

            <div class="card-header">
                All User Feedback
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover text-center align-middle">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($result->num_rows > 0): ?>

                                <?php while ($row = $result->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <?php echo (int) $row['id']; ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['name'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['email'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['subject'] ?? ''); ?>
                                        </td>

                                        <td class="message-cell">
                                            <?php echo htmlspecialchars($row['message'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($row['created_at'] ?? ''); ?>
                                        </td>

                                        <td>

                                            <form method="POST" action="">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                                <input type="hidden"
                                                       name="feedback_id"
                                                       value="<?php echo (int) $row['id']; ?>">

                                                <button type="submit"
                                                        name="delete_feedback"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this feedback?');">
                                                    Delete
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="7">
                                        No feedback records found.
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
