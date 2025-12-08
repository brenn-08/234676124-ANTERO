<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'db_config.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$_POST['name']]);
        header('Location: manage_categories.php');
        exit;
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE categories SET name=? WHERE category_id=?");
        $stmt->execute([$_POST['name'], $_POST['category_id']]);
        header('Location: manage_categories.php');
        exit;
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch category for edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { margin: 0; background: #f7f7f7; color: #1b1f3b; }

        h1, h2 { color: #1b1f3b; }

        /* Sidebar */
        .sidebar {
            position: fixed; width: 220px; height: 100%; background: #1b1f3b; color: white; padding-top: 20px;
        }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 22px; font-weight: 600; }
        .sidebar a { display: block; color: white; padding: 12px 20px; text-decoration: none; font-size: 16px; }
        .sidebar a:hover { background: #30365f; }

        /* Content */
        .content { margin-left: 240px; padding: 20px; }

        /* Forms */
        form { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 5px 0; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; }
        button { padding: 10px; border-radius: 4px; border: none; cursor: pointer; font-weight: 500; font-size: 14px; }
        button.btn { background: #1b1f3b; color: white; }
        button.btn:hover { background: #30365f; }
        button.btn-danger { background: #dc3545; color: white; }
        button.btn-danger:hover { background: #c82333; }

        /* Table */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background: #1b1f3b; color: white; }
        tr:hover { background: #f1f1f1; }
        td.actions { display: flex; gap: 5px; flex-wrap: wrap; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .content { margin-left: 0; }

            table, thead, tbody, th, td, tr { display: block; width: 100%; }
            thead tr { display: none; }

            td { position: relative; padding-left: 50%; margin-bottom: 10px; }
            td:before { content: attr(data-label); position: absolute; left: 15px; font-weight: 600; font-size: 13px; color: #1b1f3b; }

            form input, form button { font-size: 14px; }
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

        <?php
        if (isset($_SESSION['error'])) {
            echo "<div style='background:#dc3545;color:white;padding:10px;border-radius:5px;margin-bottom:15px;'>
            " . $_SESSION['error'] . "
          </div>";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            echo "<div style='background:#28a745;color:white;padding:10px;border-radius:5px;margin-bottom:15px;'>
            " . $_SESSION['success'] . "
          </div>";
            unset($_SESSION['success']);
        }
        ?>

        <h1>Manage Categories</h1>

        <a href="admin.php" class="btn" style="margin-bottom: 15px; display: inline-block; text-decoration: none;">&larr; Back</a>

        <?php if ($edit_category): ?>
            <h2>Edit Category</h2>
            <form method="POST">
                <input type="hidden" name="category_id" value="<?= $edit_category['category_id'] ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($edit_category['name']) ?>" required>
                <button type="submit" name="edit" class="btn">Update Category</button>
            </form>
        <?php else: ?>
            <h2>Add New Category</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="Category Name" required>
                <button type="submit" name="add" class="btn">Add Category</button>
            </form>
        <?php endif; ?>

        <h2>All Categories</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td data-label="ID"><?= $category['category_id'] ?></td>
                        <td data-label="Name"><?= htmlspecialchars($category['name']) ?></td>
                        <td data-label="Actions" class="actions">
                            <a href="?edit=<?= $category['category_id'] ?>"><button type="button" class="btn">Edit</button></a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $category['category_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmDelete(categoryId) {
            if (confirm('Delete this category?')) {
                window.location.href = 'delete_categories.php?category_id=' + categoryId;
            }
        }
    </script>
</body>
</html>