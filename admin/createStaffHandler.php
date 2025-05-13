<?php
require_once '../config/database.php';

// Check if the form data exists
if (isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    // Check if the email already exists
    $emailCheckQuery = "SELECT COUNT(*) FROM staff WHERE email = :email";
    $stmt = $pdo->prepare($emailCheckQuery);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $emailExists = $stmt->fetchColumn() > 0;

    if ($emailExists) {
        echo 'email_exists';
    } else {
        // Insert staff data into the database
        $query = "INSERT INTO staff (first_name, last_name, email, password) VALUES (:first_name, :last_name, :email, :password)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
} else {
    echo 'error';
}
?>
