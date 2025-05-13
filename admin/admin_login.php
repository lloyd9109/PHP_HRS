<?php
require_once '../config/database.php';
require_once 'admin.php';

session_start();

$admin = new Admin($pdo);

// If the user is already logged in, redirect them to the dashboard
if ($admin->isLoggedIn()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$usernameError = '';
$passwordError = '';
$loginError = '';
$username = ''; // To preserve the entered username

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'])); // Sanitize input
    $password = htmlspecialchars(trim($_POST['password'])); // Sanitize input
    $isValid = true;

    // Validate username
    if (empty($username)) {
        $usernameError = 'Username is required.';
        $isValid = false;
    } elseif (strlen($username) < 3) {
        $usernameError = 'Username must be at least 3 characters.';
        $isValid = false;
    }

    // Validate password
    if (empty($password)) {
        $passwordError = 'Password is required.';
        $isValid = false;
    } elseif (strlen($password) < 6) {
        $passwordError = 'Password must be at least 6 characters.';
        $isValid = false;
    }

    // If inputs are valid, attempt login
    if ($isValid && !$admin->login($username, $password)) {
        $loginError = 'Invalid Admin Credential.';
    }

    // Redirect to dashboard on successful login
    if ($isValid && empty($loginError)) {
        header('Location: ../admin/dashboard.php');
        exit();
    }

    // Redirect to the same page to clear POST data
    if (!$isValid || !empty($loginError)) {
        $_SESSION['errors'] = [
            'usernameError' => $usernameError,
            'passwordError' => $passwordError,
            'loginError' => $loginError,
            'username' => $username,
        ];
        header('Location: admin_login.php');
        exit();
    }
}

// Fetch session errors if they exist
if (isset($_SESSION['errors'])) {
    $usernameError = $_SESSION['errors']['usernameError'] ?? '';
    $passwordError = $_SESSION['errors']['passwordError'] ?? '';
    $loginError = $_SESSION['errors']['loginError'] ?? '';
    $username = $_SESSION['errors']['username'] ?? '';
    unset($_SESSION['errors']); // Clear errors after displaying them
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="admin_login.php" method="POST" novalidate>
            <div class="field">
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Admin" 
                    value="<?= htmlspecialchars($username); ?>" 
                    class="<?= !empty($usernameError) ? 'error' : ''; ?>"
                    oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z0-9@._-]/g, '')">
                
                <p id="usernameError" class="error-message"><?= htmlspecialchars($usernameError); ?></p>
            </div>

            <div class="field">
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Password" 
                    class="<?= !empty($passwordError) ? 'error' : ''; ?>"
                >
                <p id="passwordError" class="error-message"><?= htmlspecialchars($passwordError); ?></p>
            </div>
            
            <?php if (!empty($loginError)): ?>
                <p class="error-message"><?= htmlspecialchars($loginError); ?></p>
            <?php endif; ?>

            <button type="submit">Login</button>
        </form>
    </div>

</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        const usernameField = document.querySelector("input[name='username']");
        const passwordField = document.querySelector("input[name='password']");
        const usernameError = document.getElementById("usernameError");
        const passwordError = document.getElementById("passwordError");
        const errorMessages = document.querySelectorAll(".error-message");

        // Reset errors and styles when page loads
        function resetErrors() {
            usernameField.classList.remove("error");
            passwordField.classList.remove("error");
            errorMessages.forEach((error) => (error.textContent = ""));
        }

        // Reset errors on input focus
        usernameField.addEventListener("focus", resetErrors);
        passwordField.addEventListener("focus", resetErrors);

        form.addEventListener("submit", function (event) {
            let isValid = true;

            // Reset error styles
            resetErrors();

            // Validate username
            if (usernameField.value.trim() === "") {
                usernameError.textContent = "Username is required.";
                usernameField.classList.add("error");
                isValid = false;
            } else if (usernameField.value.trim().length < 3) {
                usernameError.textContent = "Username must be at least 3 characters.";
                usernameField.classList.add("error");
                isValid = false;
            }

            // Validate password
            if (passwordField.value.trim() === "") {
                passwordError.textContent = "Password is required.";
                passwordField.classList.add("error");
                isValid = false;
            } else if (passwordField.value.trim().length < 6) {
                passwordError.textContent = "Password must be at least 6 characters.";
                passwordField.classList.add("error");
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission if invalid
            }
        });
    });
</script>

<style>
        .error-message {
            color: red;
            font-size: 0.9em;
        }
        .field input.error {
            border: 1px solid red;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('../assets/admin_background2.jpg');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background-color: rgba(0, 0, 0, 0.85);
            padding: 40px;
            border-radius: 10px;
            max-width: 420px;
            width: 100%;
            color: #fff;
            position: relative;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
            animation: glowing 1.5s infinite alternate;
            margin-top: 60px;

        }

        /* Glowing border effect */
        @keyframes glowing {
            0% {
                box-shadow: 0 0 10px red, 0 0 20px #8e44ad;
            }
            100% {
                box-shadow: 0 0 20px red, 0 0 40px #8e44ad;
            }
        }

            h2 {
                margin-bottom: 20px;
                color: #28a745; /* Stylish color for heading */
                font-size: 28px;
                text-align: center; /* Centered heading */
                text-transform: uppercase;
                letter-spacing: 2px;
                font-weight: bold;
                border-bottom: 2px solid #28a745; /* Underline effect */
                padding-bottom: 10px;
                display: inline-block;
                width: 100%; /* Ensure it spans the entire width */
            }

            input {
                width: 100%;
                padding: 12px;
                margin: 10px 0;
                border-radius: 5px;
                border: 1px solid #333;
                background-color: #444;
                color: #fff;
            }

        input:focus {
            outline: none;
            border-color: #666;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin-top:10px;
        }

        button:hover {
            background-color: #218838;
        }
.error-message {
color: red;
font-size: 14px;
margin-top: 0px; /* Space above the error message */
margin-bottom: 10px; /* Space below the error message */
text-align: center; /* Align to the left */
}
    </style>