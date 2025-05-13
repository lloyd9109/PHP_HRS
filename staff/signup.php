<?php
session_start();
include '../config/database.php';

// Check if staff is already logged in, if so, redirect to manage_reservation.php
if (isset($_SESSION['staff_id'])) {
    header('Location: manage_reservation.php');
    exit();
}

$first_name = $last_name = $email = $password = $confirm_password = '';
$emailError = $passwordError = '';
$hasErrors = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if email already exists
    $sql = "SELECT * FROM staff WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $existingEmail = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingEmail) {
        $emailError = "This email is already registered.";
        $hasErrors = true;
    }

    // Password match check
    if ($password !== $confirm_password) {
        $passwordError = "Passwords do not match.";
        $hasErrors = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Please enter a valid email address.";
        $hasErrors = true;
    } elseif (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/", $password)) {
        $passwordError = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.";
        $hasErrors = true;
    }

    // Stop processing if errors exist
    if ($hasErrors) {
        echo json_encode(['status' => 'error', 'emailError' => $emailError, 'passwordError' => $passwordError]);
        exit();
    }

    // Proceed with registration if no errors
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO staff (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$first_name, $last_name, $email, $hashedPassword])) {
        $_SESSION['signup_success'] = 'Registration successful! You may now Login'; // Set session message
        echo json_encode(['status' => 'success']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error registering staff member.']);
        exit();
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
    <title>Staff Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


    <div class="container">
        <h2>Staff Sign Up</h2>
        <form action="" method="POST" onsubmit="return validateSignupForm(event)">
            <div class="form-group">
                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First Name" value="<?= htmlspecialchars($first_name) ?>" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                <p id="firstnameError" class="error-message text-danger"></p>
            </div>
            <div class="form-group">
                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last Name" value="<?= htmlspecialchars($last_name) ?>" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                <p id="lastnameError" class="error-message text-danger"></p>
            </div>
            <div class="form-group">
                <input type="text" id="signup_email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
                <p id="signupEmailError" class="error-message text-danger"><?= htmlspecialchars($emailError) ?></p>
            </div>
            <div class="form-group">
                <input type="password" id="signup_password" name="password" class="form-control" placeholder="Password">
                <p id="signupPasswordError" class="error-message text-danger"><?= htmlspecialchars($passwordError) ?></p>
            </div>
            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password">
                <p id="confirmPasswordError" class="error-message text-danger"></p>
            </div>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        <div class="toggle-form">
            <span>Already have an account? <a href="login.php">Login</a></span>
        </div>
    </div>

</body>
</html>

    <script>
function validateSignupForm(event) {
    event.preventDefault();  // Prevent the form from submitting

    let valid = true;
    document.getElementById('firstnameError').textContent = '';
    document.getElementById('lastnameError').textContent = '';
    document.getElementById('signupEmailError').textContent = '';
    document.getElementById('signupPasswordError').textContent = '';
    document.getElementById('confirmPasswordError').textContent = '';

    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const signupEmail = document.getElementById('signup_email').value.trim();
    const signupPassword = document.getElementById('signup_password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();

    // Validate email format
    if (!signupEmail) {
        document.getElementById('signupEmailError').textContent = 'Email is required.';
        valid = false;
    } else if (!/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(signupEmail)) {
        document.getElementById('signupEmailError').textContent = 'Please enter a valid email address.';
        valid = false;
    }

// Validate first name: Only letters and spaces allowed
if (!firstName) {
    document.getElementById('firstnameError').textContent = 'First name is required.';
    valid = false;
} else if (!/^[A-Za-z\s]+$/.test(firstName)) {
    document.getElementById('firstnameError').textContent = 'First name can only contain letters and spaces.';
    valid = false;
}

// Validate last name: Only letters and spaces allowed
if (!lastName) {
    document.getElementById('lastnameError').textContent = 'Last name is required.';
    valid = false;
} else if (!/^[A-Za-z\s]+$/.test(lastName)) {
    document.getElementById('lastnameError').textContent = 'Last name can only contain letters and spaces.';
    valid = false;
}


    // Validate password
    if (!signupPassword) {
        document.getElementById('signupPasswordError').textContent = 'Password is required.';
        valid = false;
    } else if (!/(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}/.test(signupPassword)) {
        document.getElementById('signupPasswordError').textContent = 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.';
        valid = false;
    }

    // Validate confirm password
    if (!confirmPassword) {
        document.getElementById('confirmPasswordError').textContent = 'Please confirm your password.';
        valid = false;
    } else if (signupPassword !== confirmPassword) {
        document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
        valid = false;
    }

    // If validation passes, check if email already exists
    if (valid) {
        const formData = new FormData();
        formData.append('signup', true);
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('email', signupEmail);
        formData.append('password', signupPassword);
        formData.append('confirm_password', confirmPassword);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                if (data.emailError) {
                    document.getElementById('signupEmailError').textContent = data.emailError;
                }
                if (data.passwordError) {
                    document.getElementById('signupPasswordError').textContent = data.passwordError;
                }

                // Reset the password fields on signup failure
                document.getElementById('signup_password').value = '';
                document.getElementById('confirm_password').value = '';
            } else if (data.status === 'success') {
                // If email is not duplicate, submit the form (this will redirect on success)
                window.location.href = 'login.php';  // Redirect after successful registration
            }
        })
        .catch(error => console.error('Error:', error));
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

    // After 3 seconds, hide and redirect to login page
    setTimeout(function() {
        messageDiv.style.display = 'none';
        window.location.href = 'login.php'; // Redirect after the message disappears
    }, 3000);
}

    </script>

<style>
.floating-message {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50; /* Green */
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
    background-image: linear-gradient(135deg, #f0e5b5, #d4af37); /* Soft gold gradient */
}

/* Container for the forms */
.container {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 420px;
    text-align: center;
    border: 1px solid #d4af37; /* Gold border */
}

/* Heading styles */
h2 {
    font-size: 24px;
    font-weight: bold;
    color: #d4af37; /* Gold color */
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Form container for login and signup forms */
.form-container {
    display: none;
}

.form-container.active {
    display: block;
}

/* Form group for individual form elements */
.form-group {
    margin-bottom: 15px;
}

/* Input fields */
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
    border-color: #d4af37; /* Gold focus border */
    outline: none;
    background-color: #fff;
}

/* Error message styling */
.error-message {
    font-size: 12px;
    color: #e74c3c;
    margin-top: 5px;
}

/* Submit buttons */
button {
    width: 100%;
    padding: 12px;
    background-color: #d4af37; /* Gold button */
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #c39c36; /* Darker gold on hover */
}

/* Links for toggling between forms */
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
    color: #c39c36; /* Darker gold on hover */
}

/* Additional styling for responsiveness */
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