<?php
// generate_hash.php - Use this to generate a password hash

$password_to_hash = '11223344'; // <-- REPLACE 'your_secure_password_here' with the actual password you want to use for login (e.g., '11223344')

$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

echo "Plain Password: " . htmlspecialchars($password_to_hash) . "<br>";
echo "Hashed Password: " . htmlspecialchars($hashed_password);

?>