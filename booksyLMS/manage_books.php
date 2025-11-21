<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
require 'db_config.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO books (author_id, category_id, title, isbn, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['author_id'], $_POST['category_id'], $_POST['title'], $_POST['isbn'], $_POST['status']]);
        header('Location: manage_books.php');
        exit;
    } elseif (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE books SET author_id=?, category_id=?, title=?, isbn=?, status=? WHERE book_id=?");
        $stmt->execute([$_POST['author_id'], $_POST['category_id'], $_POST['title'], $_POST['isbn'], $_POST['status'], $_POST['book_id']]);
        header('Location: manage_books.php');
        exit;
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id=?");
        $stmt->execute([$_POST['book_id']]);
        header('Location: manage_books.php');
        exit;
    }
}

// Fetch data
$books = $pdo->query("SELECT b.*, a.author_name, c.name AS category_name FROM books b JOIN authors a ON b.author_id = a.author_id JOIN categories c ON b.category_id = c.category_id")->fetchAll(PDO::FETCH_ASSOC);
$authors = $pdo->query("SELECT * FROM authors")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$edit_book = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General */
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            background: #f7f7f7;
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
            font-weight: 400;
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
        <h1>Manage Books</h1>

        <a href="admin.php" class="btn" style="margin-bottom: 15px; display: inline-block; text-decoration: none;">&larr; Back</a>

        <?php if ($edit_book): ?>
            <h2>Edit Book</h2>
            <form method="POST">
                <input type="hidden" name="book_id" value="<?php echo $edit_book['book_id']; ?>">
                <select name="author_id" required>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?php echo $author['author_id']; ?>" <?php if ($author['author_id'] == $edit_book['author_id'])
                               echo 'selected'; ?>>
                            <?php echo $author['author_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php if ($category['category_id'] == $edit_book['category_id'])
                               echo 'selected'; ?>>
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="title" value="<?php echo $edit_book['title']; ?>" placeholder="Book Title"
                    required>
                <input type="text" name="isbn" value="<?php echo $edit_book['isbn']; ?>" placeholder="ISBN" required>
                <select name="status">
                    <option value="available" <?php if ($edit_book['status'] == 'available')
                        echo 'selected'; ?>>Available
                    </option>
                    <option value="loaned" <?php if ($edit_book['status'] == 'loaned')
                        echo 'selected'; ?>>Loaned</option>
                </select>
                <button type="submit" name="edit" class="btn">Update Book</button>
            </form>
        <?php else: ?>
            <h2>Add New Book</h2>
            <form method="POST">
                <select name="author_id" required>
                    <option value="">Select Author</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?php echo $author['author_id']; ?>"><?php echo $author['author_name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="isbn" placeholder="ISBN" required>
                <select name="status">
                    <option value="available">Available</option>
                    <option value="loaned">Loaned</option>
                </select>
                <button type="submit" name="add" class="btn">Add Book</button>
            </form>
        <?php endif; ?>

        <h2>All Books</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>ISBN</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td data-label="ID"><?php echo $book['book_id']; ?></td>
                        <td data-label="Title"><?php echo htmlspecialchars($book['title']); ?></td>
                        <td data-label="Author"><?php echo htmlspecialchars($book['author_name']); ?></td>
                        <td data-label="Category"><?php echo htmlspecialchars($book['category_name']); ?></td>
                        <td data-label="ISBN"><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td data-label="Status"><?php echo $book['status']; ?></td>
                        <td data-label="Actions" style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <!-- Edit button -->
                            <a href="?edit=<?= $book['book_id'] ?>">
                                <button type="button" class="btn">Edit</button>
                            </a>

                            <!-- Delete button -->
                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $book['book_id'] ?>)">
                                Delete
                            </button>
                        </td>

                        <script>
                            function confirmDelete(bookId) {
                                if (confirm('Delete this book?')) {
                                    // Redirect to delete_book.php with the book ID
                                    window.location.href = 'delete_book.php?book_id=' + bookId;
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