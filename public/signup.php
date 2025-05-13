<?php
session_start();
require_once '../config/database.php';
require_once '../src/Auth.php';

// Check if user is already logged in, and redirect to index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Retrieve error messages and input values from session if they exist
$firstNameError = $_SESSION['firstNameError'] ?? '';
$lastNameError = $_SESSION['lastNameError'] ?? '';
$emailError = $_SESSION['emailError'] ?? '';
$cellphoneError = $_SESSION['cellphoneError'] ?? '';
$passwordError = $_SESSION['passwordError'] ?? '';
$confirmPasswordError = $_SESSION['confirmPasswordError'] ?? '';
$firstName = $_SESSION['firstName'] ?? '';
$lastName = $_SESSION['lastName'] ?? '';
$email = $_SESSION['email'] ?? '';
$cellphone = $_SESSION['cellphone'] ?? '';

// Clear session errors and values after retrieving them
unset($_SESSION['firstNameError'], $_SESSION['lastNameError'], $_SESSION['emailError'], $_SESSION['cellphoneError'], $_SESSION['passwordError'], $_SESSION['confirmPasswordError']);
unset($_SESSION['firstName'], $_SESSION['lastName'], $_SESSION['email'], $_SESSION['cellphone']);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cellphone = trim($_POST['cellphone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($firstName)) {
        $_SESSION['firstNameError'] = "First name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $firstName)) {
        $_SESSION['firstNameError'] = "First name can only contain letters.";
    }
    
    if (empty($lastName)) {
        $_SESSION['lastNameError'] = "Last name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $lastName)) {
        $_SESSION['lastNameError'] = "Last name can only contain letters.";
    }

    if (empty($email)) {
        $_SESSION['emailError'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['emailError'] = "Invalid email format.";
    } elseif (Auth::isEmailExists($email)) {
        $_SESSION['emailError'] = "Email already exists.";
    }

    if (empty($cellphone)) {
        $_SESSION['cellphoneError'] = "Phone number is required.";
    }

    if (empty($password)) {
        $_SESSION['passwordError'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $_SESSION['passwordError'] = "Password must be at least 8 characters.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $_SESSION['passwordError'] = "Password must contain at least one uppercase letter, one lowercase letter, and one special character.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $_SESSION['passwordError'] = "Password must contain at least one uppercase letter, one lowercase letter, and one special character.";
    } elseif (!preg_match("/[\W_]/", $password)) {
        $_SESSION['passwordError'] = "Password must contain at least one uppercase letter, one lowercase letter, and one special character.";
    }
    

    if ($password !== $confirmPassword) {
        $_SESSION['confirmPasswordError'] = "Passwords do not match.";
    }

    // Save the input values back to session in case of errors
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;
    $_SESSION['email'] = $email;
    $_SESSION['cellphone'] = $cellphone;

    // If no errors, proceed with registration
    if (empty($_SESSION['firstNameError']) && empty($_SESSION['lastNameError']) && empty($_SESSION['emailError']) && empty($_SESSION['cellphoneError']) && empty($_SESSION['passwordError']) && empty($_SESSION['confirmPasswordError'])) {
        if (Auth::register($firstName, $lastName, $email, $cellphone, $password)) {
            // Get the newly registered user's details
            global $pdo;
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
            // Initialize session with user details
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_first_name'] = $user['first_name'];
            $_SESSION['user_last_name'] = $user['last_name'];
        
            // Set success message
            $_SESSION['signup_success'] = "Signup successful! Welcome to Hotel Hive.";
        
            // Redirect to the landing page
            header("Location: index.php");
            exit;
        }
        
         else {
            echo "Registration failed. Please try again.";
        }
    } else {
        // Redirect to the signup page to avoid form resubmission warning
        header("Location: signup.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Signup</title>
    <link rel="stylesheet" href="./styles/signups.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <button class="back-button" onclick="window.location.href='index.php';">Go to Home</button>
    <div class="container">
        <div class="signup-container">
            <h2>Sign Up to Hotel Hive</h2>

            <form method="POST" action="signup.php">
                <div class="name-field">
                    <div class="field">
                        <input type="text" name="first_name" placeholder="First Name" 
                            value="<?= htmlspecialchars($firstName) ?>" 
                            oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z\s]/g, '')">
                        <p class="validation-message"><?= $firstNameError ?></p>
                    </div>
                    <div class="field">
                    <input type="text" name="last_name" placeholder="Last Name" 
                            value="<?= htmlspecialchars($lastName) ?>" 
                            oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z\s]/g, '')">
                    <p class="validation-message"><?= $lastNameError ?></p>
                    </div>
                </div>
                <div class="field">
                <input type="text" name="email" placeholder="Email" 
                        value="<?= htmlspecialchars($email) ?>" 
                        oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z0-9@._-]/g, '')">
                    <p class="validation-message"><?= $emailError ?></p>
                </div>
                <div class="field">
                    <input type="text" id="phone" name="cellphone" placeholder="Phone Number (xxx-xxx-xxxx)" maxlength="12" 
                        oninput="formatPhoneNumber(this)" value="<?= htmlspecialchars($cellphone) ?>">
                    <p class="validation-message"><?= $cellphoneError ?></p>
                </div>
                <div class="field">
                    <input type="password" name="password" placeholder="Password">
                    <p class="validation-message"><?= $passwordError ?></p>
                </div>
                <div class="field">
                    <input type="password" name="confirm_password" placeholder="Confirm Password">
                    <p class="validation-message"><?= $confirmPasswordError ?></p>
                </div>
                <button type="submit">Sign Up</button>
                <div class="footer">
                    Already have an Account? <a href="login.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>


<script>

document.getElementById('phone').addEventListener('input', function(event) {
    let phone = event.target.value;

    // Remove non-numeric characters
    phone = phone.replace(/\D/g, '');

    // Ensure the phone number starts with 9
    if (phone[0] !== '9') {
        phone = '9' + phone.slice(1);
    }

    // Format the phone number as xxx-xxx-xxxx
    if (phone.length >= 4 && phone.length <= 6) {
        phone = phone.slice(0, 3) + '-' + phone.slice(3);
    } else if (phone.length > 6) {
        phone = phone.slice(0, 3) + '-' + phone.slice(3, 6) + '-' + phone.slice(6, 10);
    }

    // Set the formatted phone number in the input field
    event.target.value = phone;
});
</script>

