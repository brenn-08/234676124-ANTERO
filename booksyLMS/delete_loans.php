<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
require 'db_config.php';

if (isset($_GET['loan_id'])) {
    $loan_id = $_GET['loan_id'];

    // Update book status back to available if loan is active
    $stmt = $pdo->prepare("SELECT book_id, status FROM loans WHERE loan_id=?");
    $stmt->execute([$loan_id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($loan && $loan['status'] === 'active') {
        $pdo->prepare("UPDATE books SET status='available' WHERE book_id=?")->execute([$loan['book_id']]);
    }

    // Delete loan
    $stmt = $pdo->prepare("DELETE FROM loans WHERE loan_id=?");
    $stmt->execute([$loan_id]);
}

header('Location: manage_loans.php');
exit;
