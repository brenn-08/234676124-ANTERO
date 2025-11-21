<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'db_config.php';

if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    // Optional: check if category has books before deletion
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE category_id=?");
    $stmt->execute([$category_id]);
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id=?");
        $stmt->execute([$category_id]);
    } else {
        // Optional: you could set a session error message here
    }
}

header('Location: manage_categories.php');
exit;
