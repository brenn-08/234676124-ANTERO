<?php
// Include DB connection
require 'db_config.php';

// -------------------------------
// NEW PASSWORD FOR ADMIN
// -------------------------------
$new_password = 'admin12345'; // Change as needed
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

// -------------------------------
// UPDATE ADMIN PASSWORD
// -------------------------------
$adminEmail = 'admin@booksy.com'; // Default admin email

$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$hashed, $adminEmail])) {
    echo "✔ Admin password has been updated successfully!";
} else {
    echo "✖ Error updating password.";
}
?>
