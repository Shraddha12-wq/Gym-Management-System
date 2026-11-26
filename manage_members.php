<?php
session_start();
require_once "config.php";

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$search = trim($_GET['search'] ?? '');

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Delete message
$delete_message = $_SESSION['delete_message'] ?? '';
unset($_SESSION['delete_message']);

// Fetch members
if ($search !== '') {

    $keyword = "%".$search."%";

    $stmt = $conn->prepare(
        "SELECT id, firstname, lastname, email, phone, gender, address, created_at
         FROM users
         WHERE firstname LIKE ?
            OR lastname LIKE ?
            OR email LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param("sss", $keyword, $keyword, $keyword);

} else {

    $stmt = $conn->prepare(
        "SELECT id, firstname, lastname, email, phone, gender, address, created_at
         FROM users
         ORDER BY id DESC"
    );
}

$stmt->execute();
$members = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Members | FitnessHealth</title>

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

.back-btn {
    background: #eb4d4b;
    color: white;
    padding: 11px 18px;
    text-decoration: none;
    border-radius: 5px;
}

.container {
    padding: 40px 5%;
}

h1 {
    margin-bottom: 12px;
}

.subtitle {
    color: #ccc;
    margin-bottom: 25px;
}

.search-box {
    background: #192a56;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.search-box form {
    display: flex;
    gap: 10px;
}

.search-box input {
    flex: 1;
    padding: 13px;
    border-radius: 5px;
    border: none;
    outline: none;
}

button {
    padding: 12px 22px;
    background: #eb4d4b;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.reset-btn {
    background: #30336b;
    color: white;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 5px;
}

.table-box {
    background: #192a56;
    padding: 20px;
    border-radius: 10px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 950px;
}

th {
    background: #30336b;
    padding: 15px;
    text-align: left;
    color: #f6e58d;
}

td {
    padding: 14px;
    border-bottom: 1px solid #414b75;
    color: #eee;
}

tr:hover {
    background: #252f5e;
}

.empty {
    text-align: center;
    padding: 30px;
    color: #f6e58d;
}

.count {
    margin-bottom: 15px;
    color: #ddd;
}

footer {
    text-align: center;
    padding: 25px;
    margin-top: 40px;
    background: #192a56;
    color: #ccc;
    border-top: 2px solid #eb4d4b;
}

@media(max-width: 600px) {

    header {
        padding: 18px;
    }

    .container {
        padding: 25px 15px;
    }

    .search-box form {
        flex-direction: column;
    }

}

</style>

</head>

<body>

<header>

    <div class="logo">
        Fitness<span>Health</span> Admin
    </div>

    <a href="admin_dashboard.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
        Dashboard
    </a>

</header>

<div class="container">

    <h1>
        <i class="fa-solid fa-users"></i>
        Manage Members
    </h1>

    <?php if ($delete_message): ?>

        <p style="background:#192a56;color:#f6e58d;
                padding:15px;border-radius:6px;margin:15px 0;">

            <?= htmlspecialchars($delete_message) ?>

        </p>

    <?php endif; ?>

    <p class="subtitle">
        View and search all registered gym members.
    </p>

    <!-- Search -->

    <div class="search-box">

        <form method="GET">

            <input
                type="text"
                name="search"
                placeholder="Search by name or email..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <button type="submit">
                <i class="fa-solid fa-search"></i>
                Search
            </button>

            <a href="manage_members.php" class="reset-btn">
                Reset
            </a>

        </form>

    </div>

    <!-- Members Table -->

    <div class="table-box">

        <p class="count">
            <i class="fa-solid fa-users"></i>
            Total Members Found: <?= $members->num_rows ?>
        </p>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Registration Date</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php if ($members->num_rows > 0): ?>

                    <?php while ($row = $members->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= (int)$row['id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['firstname'].' '.$row['lastname']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['email']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['phone'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['gender'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['address'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['created_at'] ?? '') ?>
                            </td>

                            <td style="white-space:nowrap;">

                                <a href="edit_member.php?id=<?= (int)$row['id'] ?>"
                                style="color:#f6e58d;text-decoration:none;">
                                    Edit
                                </a>

                                <form action="delete_member.php"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this member?');">

                                    <input type="hidden" name="id"
                                        value="<?= (int)$row['id'] ?>">

                                    <input type="hidden" name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                    <button type="submit"
                                            style="background:#eb4d4b;padding:7px 12px;margin-left:8px;">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="empty">
                            No Members Found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<footer>

    <p>© 2026 FitnessHealth Admin Panel. All Rights Reserved.</p>

</footer>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>