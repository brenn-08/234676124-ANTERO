<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'db_config.php';

if (isset($_GET['author_id'])) {
    $author_id = $_GET['author_id'];

    // Optional: prevent deletion if author has books
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE author_id=?");
    $stmt->execute([$author_id]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("DELETE FROM authors WHERE author_id=?");
        $stmt->execute([$author_id]);
    } else {
        // Optional: set session error for feedback
        $_SESSION['error'] = "Cannot delete author with assigned books!";
    }
}

header('Location: manage_authors.php');
exit;
