<?php
// Include the PDO database connection
require 'db_config.php';

if (isset($_GET['book_id'])) {
    $book_id = intval($_GET['book_id']); // sanitize input

    try {
        // Prepare and execute the DELETE statement using PDO
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = :book_id");
        $stmt->execute(['book_id' => $book_id]);

        // Redirect back to the books page with a success message
        header("Location: manage_books.php?message=Book+deleted+successfully");
        exit;
    } catch (PDOException $e) {
        die("Error deleting book: " . $e->getMessage());
    }
} else {
    echo "No book ID provided.";
}
?>
