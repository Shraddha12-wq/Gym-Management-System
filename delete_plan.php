<?php
session_start();
require_once "config.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Allow only POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_plans.php");
    exit();
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Invalid request. Please refresh the page.");
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: manage_plans.php");
    exit();
}

// Check whether plan has memberships
$stmt = $conn->prepare(
    "SELECT id FROM memberships WHERE plan_id = ? LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$has_memberships = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($has_memberships) {

    $_SESSION['plan_message'] =
        "This plan has membership history. Please make it Inactive instead of deleting.";

} else {

    // Delete plan
    $stmt = $conn->prepare(
        "DELETE FROM membership_plans WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute() && $stmt->affected_rows === 1) {

        $_SESSION['plan_message'] =
            "Membership plan deleted successfully.";

    } else {

        $_SESSION['plan_message'] =
            "Unable to delete membership plan.";
    }

    $stmt->close();
}

header("Location: manage_plans.php");
exit();
?>