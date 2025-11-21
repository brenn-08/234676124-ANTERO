<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
require 'db_config.php';

// Fetch counts
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$active_members = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND membership_status='active'")->fetchColumn();
$books_loaned = $pdo->query("SELECT COUNT(*) FROM loans WHERE status='active'")->fetchColumn();
$overdue = $pdo->query("SELECT COUNT(*) FROM loans WHERE status='active' AND return_date < CURDATE()")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Management System - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f7f7f7; }
        .sidebar { position: fixed; width: 220px; height: 100%; background: #1b1f3b; color: white; padding-top: 20px; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 22px; font-weight: 600; }
        .sidebar a { display: block; color: white; padding: 12px 20px; text-decoration: none; font-size: 16px; font-weight: 400; }
        .sidebar a:hover { background: #30365f; }
        .content { margin-left: 240px; padding: 20px; }
        h1 { color: #333; font-weight: 600; }
        .cards { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .card { background: white; padding: 20px; width: 200px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .card h3 { margin: 0; color: #1b1f3b; font-size: 18px; font-weight: 500; }
        .card p { font-size: 28px; margin: 10px 0 0; color: #009578; font-weight: 600; }
        .btn { font-family: 'Poppins', sans-serif; padding: 10px 16px; background: #1b1f3b; color: white; border: none; cursor: pointer; font-size: 15px; border-radius: 4px; font-weight: 500; }
        .btn:hover { background: #30365f; }
        .quick-actions { margin-top: 30px; }
        .quick-actions button { margin-right: 10px; }
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
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['name']; ?>.</p>
        <div class="cards">
            <div class="card"><h3>Total Books</h3><p><?php echo $total_books; ?></p></div>
            <div class="card"><h3>Active Members</h3><p><?php echo $active_members; ?></p></div>
            <div class="card"><h3>Books Loaned</h3><p><?php echo $books_loaned; ?></p></div>
            <div class="card"><h3>Overdue</h3><p><?php echo $overdue; ?></p></div>
        </div>
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <a href="manage_books.php?action=add"><button class="btn">+ Add New Book</button></a>
            <a href="manage_users.php?action=add"><button class="btn">+ Register Member</button></a>
            <a href="manage_loans.php?action=add"><button class="btn">+ Issue Loan</button></a>
        </div>
    </div>
</body>
</html>