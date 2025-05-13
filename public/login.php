<?php
session_start();
require_once '../config/database.php';
require_once '../src/Auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Check if the success message is set in the session
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    // Remove the success message from the session after it's been displayed
    unset($_SESSION['successMessage']);
} else {
    $successMessage = '';
}

$errorMessage = '';
$email = ''; 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        // Trim email to remove leading/trailing spaces
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email)) {
            $errorMessage = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } elseif (empty($password)) {
            $errorMessage = 'Password is required.';
        } else {
            $user = Auth::isEmailExists($email);
            if (!$user) {
                $errorMessage = 'No account is associated with this email address.';
            } elseif (!Auth::login($email, $password)) {
                $errorMessage = 'Incorrect password. Please try again.';
            } else {
                // Successful login
                $_SESSION['successMessage'] = 'You have successfully logged in!'; // Add success message
                header('Location: index.php');
                exit;
            }
        }
    }
    $_SESSION['email'] = $email;
    $_SESSION['errorMessage'] = $errorMessage;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


$email = $_SESSION['email'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';
unset($_SESSION['email'], $_SESSION['errorMessage']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Login</title>
    <link rel="stylesheet" href="./styles/logins.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>

<?php if ($successMessage): ?>
        <div id="successNotification" class="success-message"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <button class="back-button" onclick="window.location.href='index.php';">Go to Home</button>
    <div class="container">
        
        <div class="login-container">
            <h2>Sign In to Hotel Hive</h2>

            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm" onsubmit="return validateForm()">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="field">
                    <input type="email" id="email" name="email" placeholder="Email" 
                        value="<?= htmlspecialchars($email ?? '') ?>" 
                        oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z0-9@._-]/g, '')">
                    <p id="emailError" class="error-message"></p>
                </div>
                <div class="field">
                    <input type="password" id="password" name="password" placeholder="Password" >
                    <p id="passwordError" class="error-message"></p>
                </div>

                <?php if (isset($errorMessage)): ?>
                    <p class="error-message"><?= htmlspecialchars($errorMessage); ?></p>
                <?php endif; ?>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>

                <button type="submit">LOGIN</button>

            </form>
        </div>

        <div class="login-info-container">
            <h2>Welcome to Hotel Hive!</h2>
            <p>Please sign in to access your account and manage your bookings. Log in to enjoy a seamless experience with personalized services and exclusive offers.</p>
            <p class="login-prompt">Don’t have an account yet? <a href="signup.php">Sign up </a> now and start booking your perfect stay today!</p>
        </div>
    </div>

    <script>
function validateForm() {
    let email = document.getElementById('email').value.trim();  // Trim spaces
    let password = document.getElementById('password').value;
    let emailError = document.getElementById('emailError');
    let passwordError = document.getElementById('passwordError');
    let isValid = true;

    emailError.textContent = '';
    passwordError.textContent = '';

    if (!email) {
        emailError.textContent = 'Email is required.';
        isValid = false;
    } else if (!/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(email)) {
        emailError.textContent = 'Please enter a valid email address.';
        isValid = false;
    }

    if (!password) {
        passwordError.textContent = 'Password is required.';
        isValid = false;
    }

    // Trim email before submitting the form if valid
    if (isValid) {
        document.getElementById('email').value = email;  // Update the email field with trimmed value
    }

    return isValid;
}


    </script>
    <style>
        .success-message {
            position: fixed; /* Float above the page */
            top: 10%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Correct centering */
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            z-index: 1000; /* Ensure it's above other elements */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            animation: fadeOut 4s forwards; /* Smooth fade-out effect */
            text-align: center; /* Center text inside the message */
            max-width: 80%; /* Prevent message from overflowing on smaller screens */
        }
        .success-message{
            background-color: #dff0d8; /* Light green background */
            color: #3c763d; /* Dark green text */
            border: 1px solid #d6e9c6;
        }

        /* Keyframes for fade-out effect */
                /* Fade-out animation */
                @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            80% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -60%); /* Slight upward slide on fade-out */
            }
        }


    </style>
</body>
</html>