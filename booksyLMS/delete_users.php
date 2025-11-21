<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'db_config.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        header("Location: manage_users.php?message=User+deleted+successfully");
        exit;
    } catch (PDOException $e) {
        die("Error deleting user: " . $e->getMessage());
    }
} else {
    echo "No user ID provided.";
}
?>
