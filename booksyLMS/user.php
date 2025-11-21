<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}
require 'db_config.php';

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Fetch counts
$books_borrowed = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status='active'");
$books_borrowed->execute([$user_id]);
$books_borrowed_count = $books_borrowed->fetchColumn();

$overdue = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status='active' AND return_date < CURDATE()");
$overdue->execute([$user_id]);
$overdue_count = $overdue->fetchColumn();

// Handle loan request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_loan'])) {
    $book_id = $_POST['book_id'];
    $checkout_date = date('Y-m-d');
    $return_date = date('Y-m-d', strtotime('+14 days'));  // Default 2 weeks; can be configurable
    $stmt = $pdo->prepare("INSERT INTO loans (book_id, user_id, checkout_date, return_date, status) VALUES (?, ?, ?, ?, 'active')");
    if ($stmt->execute([$book_id, $user_id, $checkout_date, $return_date])) {
        // Update book status
        $pdo->prepare("UPDATE books SET status='loaned' WHERE book_id=?")->execute([$book_id]);
        $success = "Loan requested successfully!";
    } else {
        $error = "Failed to request loan.";
    }
}

// Handle return loan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_loan'])) {
    $loan_id = $_POST['loan_id'];
    $stmt = $pdo->prepare("UPDATE loans SET status='returned', return_date=CURDATE() WHERE loan_id=? AND user_id=?");
    if ($stmt->execute([$loan_id, $user_id])) {
        // Get book_id and update status
        $book_stmt = $pdo->prepare("SELECT book_id FROM loans WHERE loan_id=?");
        $book_stmt->execute([$loan_id]);
        $book_id = $book_stmt->fetchColumn();
        $pdo->prepare("UPDATE books SET status='available' WHERE book_id=?")->execute([$book_id]);
        $success = "Book returned successfully!";
    } else {
        $error = "Failed to return book.";
    }
}

// Fetch user's loans
$user_loans = $pdo->prepare("SELECT l.loan_id, b.title, a.author_name, l.checkout_date, l.return_date, l.status FROM loans l JOIN books b ON l.book_id = b.book_id JOIN authors a ON b.author_id = a.author_id WHERE l.user_id = ?");
$user_loans->execute([$user_id]);
$user_loans = $user_loans->fetchAll(PDO::FETCH_ASSOC);

// Fetch available books (with search filter)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT b.book_id, b.title, a.author_name, c.name AS category, b.status FROM books b JOIN authors a ON b.author_id = a.author_id JOIN categories c ON b.category_id = c.category_id WHERE b.status='available'";
if ($search) {
    $query .= " AND b.title LIKE ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->query($query);
}
$available_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Management System - User Dashboard</title>
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
        .card p { font-size: 20px; margin: 10px 0 0; color: #009578; font-weight: 600; }
        .btn { font-family: 'Poppins', sans-serif; padding: 10px 16px; background: #1b1f3b; color: white; border: none; cursor: pointer; font-size: 15px; border-radius: 4px; font-weight: 500; }
        .btn:hover { background: #30365f; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .book-list, .loan-list { margin-top: 30px; }
        .book-list table, .loan-list table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .book-list th, .book-list td, .loan-list th, .loan-list td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .book-list th, .loan-list th { background: #1b1f3b; color: white; }
        .book-list tr:hover, .loan-list tr:hover { background: #f1f1f1; }
        .search-bar { margin-top: 20px; margin-bottom: 20px; }
        .search-bar input { padding: 8px 12px; width: 250px; border-radius: 4px; border: 1px solid #ccc; font-family: 'Poppins', sans-serif; }
        .search-bar button { padding: 8px 12px; border-radius: 4px; border: none; background: #1b1f3b; color: white; cursor: pointer; font-weight: 500; font-family: 'Poppins', sans-serif; }
        .search-bar button:hover { background: #30365f; }
        .message { margin-top: 20px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Booksy LMS</h2>
        <a href="#dashboard">Dashboard</a>
        <a href="#available-books">Available Books</a>
        <a href="#my-loans">My Loans</a>
        <a href="#profile">Profile</a>
        <a href="#support">Support</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <div id="dashboard">
            <h1>User Dashboard</h1>
            <p>Welcome, <?php echo $name; ?>.</p>
            <?php if (isset($success)): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
            <div class="cards">
                <div class="card">
                    <h3>Books Borrowed</h3>
                    <p><?php echo $books_borrowed_count; ?></p>
                </div>
                <div class="card">
                    <h3>Overdue Books</h3>
                    <p><?php echo $overdue_count; ?></p>
                </div>
                <div class="card">
                    <h3>Available Credits</h3>
                    <p>5</p>  <!-- Static; can make dynamic if adding credits system -->
                </div>
            </div>
        </div>

        <div id="available-books">
            <div class="search-bar">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search books by title..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="book-list">
                <h2>Available Books</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_books as $book): ?>
                            <tr>
                                <td><?php echo $book['title']; ?></td>
                                <td><?php echo $book['author_name']; ?></td>
                                <td><?php echo $book['category']; ?></td>
                                <td><?php echo $book['status']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <button type="submit" name="request_loan" class="btn">Request</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="my-loans" class="loan-list">
            <h2>My Loans</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Checkout Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_loans as $loan): ?>
                        <tr>
                            <td><?php echo $loan['title']; ?></td>
                            <td><?php echo $loan['author_name']; ?></td>
                            <td><?php echo $loan['checkout_date']; ?></td>
                            <td><?php echo $loan['return_date'] ?: 'N/A'; ?></td>
                            <td><?php echo $loan['status']; ?></td>
                            <td>
                                <?php if ($loan['status'] == 'active'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                        <button type="submit" name="return_loan" class="btn btn-danger">Return</button>
                                    </form>
                                <?php else: ?>
                                    Returned
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="profile">
            <h2>Profile</h2>
            <p>Name: <?php echo $name; ?></p>
            <p>Email: <?php echo $_SESSION['email'] ?? 'N/A'; ?></p>  <!-- Assuming email in session; add if needed -->
            <!-- Add edit profile form if desired -->
        </div>

        <div id="support">
            <h2>Support</h2>
            <p>Contact admin for help.</p>
        </div>
    </div>
</body>
</html>