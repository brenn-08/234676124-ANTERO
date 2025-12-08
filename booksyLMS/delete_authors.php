<?php
session_start();

// Check admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'db_config.php';

if (isset($_GET['author_id']) && is_numeric($_GET['author_id'])) {

    $author_id = $_GET['author_id'];

    // Check if author has assigned books
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE author_id = ?");
    $stmt->execute([$author_id]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {

        // Delete author
        $stmt = $pdo->prepare("DELETE FROM authors WHERE author_id = ?");
        if ($stmt->execute([$author_id])) {
            $_SESSION['success'] = "Author deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete author. Please try again.";
        }
    } else {

        // Feedback if deletion is not allowed
        $_SESSION['error'] = "Cannot delete author with assigned books!";
    }
}

// Redirect back
header('Location: manage_authors.php');
exit;