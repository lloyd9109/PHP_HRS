<?php
session_start();
require_once '../src/Staff_Auth.php';
include '../config/database.php';

$email = '';
$password = '';
$email_error = '';
$password_error = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = 'Invalid email format.';
    } else {
        // Check if the email exists in the database
        $sql = "SELECT * FROM staff WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the email is not found
        if (!$staff) {
            $email_error = 'Email does not exist.';
        } elseif (!password_verify($password, $staff['password'])) {
            // If password verification fails
            $password_error = 'Incorrect password.';
        } else {
            // Successful login
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_name'] = $staff['first_name'] . ' ' . $staff['last_name'];
            header("Location: staff_dashboard.php");
            exit;
        }
    }
}

if (isset($_SESSION['signup_success'])) {
    echo '<script type="text/javascript">
        window.onload = function() {
            showFloatingMessage("' . $_SESSION['signup_success'] . '");
        };
    </script>';
    unset($_SESSION['signup_success']); // Clear the session variable after displaying the message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h2>Staff Login</h2>
        <form action="" method="POST" onsubmit="return validateLoginForm()">
            <div class="form-group">
                <input type="text" id="login_email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>">
                <p id="loginEmailError" class="error-message text-danger"><?= $email_error ?></p>
            </div>
            <div class="form-group">
                <input type="password" id="login_password" name="password" class="form-control" placeholder="Password">
                <p id="loginPasswordError" class="error-message text-danger"><?= $password_error ?></p>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="toggle-form text-center mt-3">
            <span>Don't have an account? <a href="signup.php">Sign Up</a></span>
        </div>
    </div>

</body>
</html>

<script>
    function validateLoginForm() {
        let valid = true;
        document.getElementById('loginEmailError').textContent = '';
        document.getElementById('loginPasswordError').textContent = '';

        const loginEmail = document.getElementById('login_email').value.trim();
        const loginPassword = document.getElementById('login_password').value.trim();

        // Validate email
        if (!loginEmail) {
            document.getElementById('loginEmailError').textContent = 'Email is required.';
            valid = false;
        } else if (!/\S+@\S+\.\S+/.test(loginEmail)) {
            document.getElementById('loginEmailError').textContent = 'Invalid email format.';
            valid = false;
        }

        // Validate password
        if (!loginPassword) {
            document.getElementById('loginPasswordError').textContent = 'Password is required.';
            valid = false;
        }

        return valid;
    }

    function showFloatingMessage(message) {
        // Create the floating message element
        const messageDiv = document.createElement('div');
        messageDiv.className = 'floating-message';
        messageDiv.textContent = message;

        // Append to body
        document.body.appendChild(messageDiv);

        // After 3 seconds, hide the message
        setTimeout(function() {
            messageDiv.style.display = 'none';
        }, 3000); // Hide the message after 3 seconds
    }
</script>


<style>
.floating-message {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50; 
    color: white;
    padding: 15px;
    border-radius: 5px;
    font-size: 16px;
    z-index: 1000;
    display: block;
    opacity: 1;
    transition: opacity 0.3s ease;
}


body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fc;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-image: linear-gradient(135deg, #f0e5b5, #d4af37); 
}


.container {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 420px;
    text-align: center;
    border: 1px solid #d4af37; 
}


h2 {
    font-size: 24px;
    font-weight: bold;
    color: #d4af37;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}


.form-container {
    display: none;
}

.form-container.active {
    display: block;
}


.form-group {
    margin-bottom: 15px;
}


.form-control {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #e6e6e6;
    border-radius: 5px;
    box-sizing: border-box;
    background-color: #f9f9f9;
    color: #333;
}

.form-control:focus {
    border-color: #d4af37; 
    outline: none;
    background-color: #fff;
}


.error-message {
    font-size: 12px;
    color: #e74c3c;
    margin-top: 5px;
}


button {
    width: 100%;
    padding: 12px;
    background-color: #d4af37; 
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #c39c36;
}


.toggle-form {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #333;
}

.toggle-form a {
    color: #d4af37;
    text-decoration: none;
    font-weight: bold;
}

.toggle-form a:hover {
    text-decoration: underline;
    color: #c39c36; 
}


@media (max-width: 480px) {
    .container {
        width: 90%;
        padding: 20px;
    }

    h2 {
        font-size: 20px;
    }

    button {
        font-size: 16px;
    }
}
</style>