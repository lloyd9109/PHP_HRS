<?php

require_once '../config/database.php';


$hashedPassword = password_hash('123456789', PASSWORD_DEFAULT);


$sql = "INSERT INTO admin (username, password) VALUES ('Admin', :password)";

$stmt = $pdo->prepare($sql);
$stmt->execute(['password' => $hashedPassword]);

echo "Admin account created successfully.";
?>
