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
        $stmt = $pdo->prepare("INSERT INTO loans (book_id, user_id, checkout_date, return_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['book_id'], $_POST['user_id'], $_POST['checkout_date'], $_POST['return_date'], $_POST['status']]);
        if ($_POST['status'] == 'active') {
            $pdo->prepare("UPDATE books SET status='loaned' WHERE book_id=?")->execute([$_POST['book_id']]);
        }
        header('Location: manage_loans.php');
        exit;
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE loans SET book_id=?, user_id=?, checkout_date=?, return_date=?, status=? WHERE loan_id=?");
        $stmt->execute([$_POST['book_id'], $_POST['user_id'], $_POST['checkout_date'], $_POST['return_date'], $_POST['status'], $_POST['loan_id']]);
        $pdo->prepare("UPDATE books SET status=? WHERE book_id=?")->execute([$_POST['status'] == 'active' ? 'loaned' : 'available', $_POST['book_id']]);
        header('Location: manage_loans.php');
        exit;
    }
}

// Fetch data
$loans = $pdo->query("SELECT l.*, b.title, u.name AS user_name FROM loans l JOIN books b ON l.book_id = b.book_id JOIN users u ON l.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);
$books = $pdo->query("SELECT book_id, title FROM books")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT user_id, name FROM users WHERE role='user'")->fetchAll(PDO::FETCH_ASSOC);

// Handle edit loan
$edit_loan = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM loans WHERE loan_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_loan = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loans - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
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
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }

        .btn {
            background: #1b1f3b;
            color: white;
        }

        .btn:hover {
            background: #30365f;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
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
        <h1>Manage Loans</h1>

        <?php if ($edit_loan): ?>
            <h2>Edit Loan</h2>
            <form method="POST">
                <input type="hidden" name="loan_id" value="<?= $edit_loan['loan_id'] ?>">
                <select name="book_id" required>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= $book['book_id'] ?>" <?= $book['book_id'] == $edit_loan['book_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($book['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>" <?= $user['user_id'] == $edit_loan['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="checkout_date" value="<?= $edit_loan['checkout_date'] ?>" required>
                <input type="date" name="return_date" value="<?= $edit_loan['return_date'] ?>">
                <select name="status">
                    <option value="active" <?= $edit_loan['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="returned" <?= $edit_loan['status'] == 'returned' ? 'selected' : '' ?>>Returned</option>
                </select>
                <button type="submit" name="edit" class="btn">Update Loan</button>
            </form>
        <?php else: ?>
            <h2>Add New Loan</h2>
            <form method="POST">
                <select name="book_id" required>
                    <option value="">Select Book</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= $book['book_id'] ?>"><?= htmlspecialchars($book['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="user_id" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="checkout_date" value="<?= date('Y-m-d') ?>" required>
                <input type="date" name="return_date" placeholder="Return Date (optional)">
                <select name="status">
                    <option value="active">Active</option>
                    <option value="returned">Returned</option>
                </select>
                <button type="submit" name="add" class="btn">Add Loan</button>
            </form>
        <?php endif; ?>

        <h2>All Loans</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Book</th>
                    <th>User</th>
                    <th>Checkout Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td data-label="ID"><?= $loan['loan_id'] ?></td>
                        <td data-label="Book"><?= htmlspecialchars($loan['title']) ?></td>
                        <td data-label="User"><?= htmlspecialchars($loan['user_name']) ?></td>
                        <td data-label="Checkout Date"><?= $loan['checkout_date'] ?></td>
                        <td data-label="Return Date"><?= $loan['return_date'] ?: 'N/A' ?></td>
                        <td data-label="Status"><?= $loan['status'] ?></td>
                        <td data-label="Actions" class="actions">
                            <a href="?edit=<?= $loan['loan_id'] ?>"><button type="button" class="btn">Edit</button></a>
                            <button type="button" class="btn btn-danger"
                                onclick="confirmDelete(<?= $loan['loan_id'] ?>)">Delete</button>
                        </td>

                        <script>
                            function confirmDelete(loanId) {
                                if (confirm('Delete this loan?')) {
                                    window.location.href = 'delete_loans.php?loan_id=' + loanId;
                                }
                            }
                        </script>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>