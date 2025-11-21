<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
require 'db_config.php';

// Handle add/edit actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, membership_status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['email'], $password, $_POST['role'], $_POST['membership_status']]);
        header('Location: manage_users.php');
        exit;
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, membership_status=? WHERE user_id=?");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['role'], $_POST['membership_status'], $_POST['user_id']]);
        header('Location: manage_users.php');
        exit;
    }
}

// Fetch users
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Handle edit user
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            background: #f7f7f7;
            color: #1b1f3b;
        }

        h1,
        h2 {
            color: #1b1f3b;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            width: 220px;
            height: 100%;
            background: #1b1f3b;
            color: white;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 22px;
            font-weight: 600;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            font-size: 16px;
        }

        .sidebar a:hover {
            background: #30365f;
        }

        /* Content */
        .content {
            margin-left: 240px;
            padding: 20px;
        }

        /* Forms */
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        input,
        select,
        button {
            font-family: 'Poppins', sans-serif;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input::placeholder {
            color: #888;
        }

        button {
            padding: 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
        }

        button.btn {
            background: #1b1f3b;
            color: white;
        }

        button.btn:hover {
            background: #30365f;
        }

        button.btn-danger {
            background: #dc3545;
            color: white;
        }

        button.btn-danger:hover {
            background: #c82333;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        th {
            background: #1b1f3b;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        td.actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        /* Wrap long text like password hash */
        td[data-label="Password"] {
            word-break: break-all;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            thead tr {
                display: none;
            }

            td {
                position: relative;
                padding-left: 50%;
                margin-bottom: 10px;
            }

            td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                font-weight: 600;
                font-size: 13px;
                color: #1b1f3b;
            }

            form input,
            form select,
            form button {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Booksy LMS</h2>
        <a href="admin.php">Dashboard</a>
        <a href="manage_books.php">Books</a>
        <a href="manage_users.php">Users</a>
        <a href="manage_loans.php">Loans</a>
        <a href="manage_authors.php">Authors</a>
        <a href="manage_categories.php">Categories</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Manage Users</h1>

        <a href="admin.php" class="btn" style="margin-bottom: 15px; display: inline-block; text-decoration: none;">&larr; Back</a>

        <?php if ($edit_user): ?>
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $edit_user['user_id'] ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($edit_user['name']) ?>" placeholder="Name" required>
                <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email']) ?>" placeholder="Email" required>
                <select name="role">
                    <option value="user" <?= $edit_user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $edit_user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <select name="membership_status">
                    <option value="active" <?= $edit_user['membership_status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $edit_user['membership_status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button type="submit" name="edit" class="btn">Update User</button>
            </form>
        <?php else: ?>
            <h2>Add New User</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <select name="membership_status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="submit" name="add" class="btn">Add User</button>
            </form>
        <?php endif; ?>

        <h2>All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Password Hash</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td data-label="ID"><?= $user['user_id'] ?></td>
                        <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                        <td data-label="Role"><?= $user['role'] ?></td>
                        <td data-label="Status"><?= $user['membership_status'] ?></td>
                        <td data-label="Password"><?= $user['password'] ?></td>
                        <td data-label="Actions" class="actions">
                            <a href="?edit=<?= $user['user_id'] ?>"><button type="button" class="btn">Edit</button></a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $user['user_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmDelete(userId) {
            if (confirm('Delete this user?')) {
                window.location.href = 'delete_users.php?user_id=' + userId;
            }
        }
    </script>
</body>

</html>
