<?php
session_start();
include '../config/database.php';

// Process signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO staff (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$first_name, $last_name, $email, $password])) {
        echo "<script>alert('Staff member registered successfully.');</script>";
    } else {
        echo "<script>alert('Error registering staff member.');</script>";
    }
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM staff WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff && password_verify($password, $staff['password'])) {
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['staff_name'] = $staff['first_name'] . ' ' . $staff['last_name'];
        header("Location: staff_dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login & Signup</title>
    <link rel="stylesheet" href="style.css"> <!-- Add your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            padding: 20px;
            width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            margin-top: 10px;
            display: block;
            color: #555;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            width: 100%;
            margin: 10px 0;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            padding: 10px;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .toggle-form {
            text-align: center;
            margin-top: 10px;
        }
        .toggle-form a {
            color: #007bff;
            text-decoration: none;
        }
        .toggle-form a:hover {
            text-decoration: underline;
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
    </style>
    <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            loginForm.classList.toggle('active');
            signupForm.classList.toggle('active');
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Staff Portal</h2>
        
        <div id="login-form" class="form-container active">
            <form action="" method="POST">
                <label for="login-email">Email:</label>
                <input type="email" id="login-email" name="email" required>

                <label for="login-password">Password:</label>
                <input type="password" id="login-password" name="password" required>

                <button type="submit" name="login">Login</button>
            </form>
            <div class="toggle-form">
                <span>Don't have an account? <a href="#" onclick="toggleForms()">Sign Up</a></span>
            </div>
        </div>

        <div id="signup-form" class="form-container">
            <form action="" method="POST">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>

                <label for="signup-email">Email:</label>
                <input type="email" id="signup-email" name="email" required>

                <label for="signup-password">Password:</label>
                <input type="password" id="signup-password" name="password" required>

                <button type="submit" name="signup">Sign Up</button>
            </form>
            <div class="toggle-form">
                <span>Already have an account? <a href="#" onclick="toggleForms()">Login</a></span>
            </div>
        </div>
    </div>
</body>
</html>
