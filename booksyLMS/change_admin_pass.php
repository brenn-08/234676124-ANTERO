<?php

// For the database connection
require 'db_config.php';

// Setting up new password for admin
$new_password = 'admin12345'; // Change as needed
$hashed = password_hash($new_password, PASSWORD_DEFAULT);


// Default admin email
$adminEmail = 'admin@booksy.com'; 

$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$hashed, $adminEmail])) {
    echo "✔ Admin password has been updated successfully!";
} else {
    echo "✖ Error updating password.";
}
?>