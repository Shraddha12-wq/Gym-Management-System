<?php
session_start();
require_once "config.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Only POST requests allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_members.php");
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
    header("Location: manage_members.php");
    exit();
}

// Check membership records
$stmt = $conn->prepare(
    "SELECT id FROM memberships WHERE user_id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$membership = $stmt->get_result()->num_rows > 0;
$stmt->close();

// Check payment records
$stmt = $conn->prepare(
    "SELECT id FROM payments WHERE user_id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$payment = $stmt->get_result()->num_rows > 0;
$stmt->close();

// Prevent deletion if records exist
if ($membership || $payment) {

    $_SESSION['delete_message'] =
        "This member has membership or payment history. Deletion is not allowed.";

    header("Location: manage_members.php");
    exit();
}

// Delete member
$stmt = $conn->prepare(
    "DELETE FROM users WHERE id = ?"
);

$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows === 1) {

    $_SESSION['delete_message'] =
        "Member deleted successfully.";

} else {

    $_SESSION['delete_message'] =
        "Unable to delete member.";
}

$stmt->close();

header("Location: manage_members.php");
exit();
?>