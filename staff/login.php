<?php
session_start();
include '../config/database.php';

// Check if staff is already logged in, if so, redirect to manage_reservation.php
if (isset($_SESSION['staff_id'])) {
    header('Location: manage_reservation.php');
    exit();
}

$email = $password = '';
$emailError = $passwordError = '';
$hasErrors = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate email
    if (empty($email)) {
        $emailError = "Email is required.";
        $hasErrors = true;
    } elseif (preg_match('/^\s/', $email)) {  // Check for leading spaces
        $emailError = "Email cannot have leading spaces.";
        $hasErrors = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Please enter a valid email address.";
        $hasErrors = true;
    }


    // Validate password
    if (empty($password)) {
        $passwordError = "Password is required.";
        $hasErrors = true;
    }

    // Stop processing if errors exist
    if ($hasErrors) {
        echo json_encode(['status' => 'error', 'emailError' => $emailError, 'passwordError' => $passwordError]);
        exit();
    }

    // Check if email exists
    $sql = "SELECT * FROM staff WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        // If email does not exist, return error
        $emailError = "No account found with that email address.";
        echo json_encode(['status' => 'error', 'emailError' => $emailError]);
        exit();
    }

    // Check if password matches
    if (password_verify($password, $staff['password'])) {
        // Set session variables
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['first_name'] = $staff['first_name'];
        $_SESSION['last_name'] = $staff['last_name'];
        $_SESSION['email'] = $staff['email'];

        // Redirect to staff_dashboard.php
        echo json_encode(['status' => 'success', 'redirect' => 'staff_dashboard.php']);
        exit();
    } else {
        $passwordError = "Incorrect password.";
        echo json_encode(['status' => 'error', 'passwordError' => $passwordError]);
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
    <title>Staff Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Staff Login</h2>
        <form action="" method="POST" onsubmit="return validateLoginForm(event)">
            <div class="form-group">
                <input type="text" id="login_email" name="email" class="form-control" placeholder="Email" 
                value="<?= htmlspecialchars($email) ?>"
                oninput="this.value = this.value.replace(/^\s+/g, '').replace(/[^a-zA-Z0-9@._-]/g, '')">
                <p id="loginEmailError" class="error-message text-danger"><?= htmlspecialchars($emailError) ?></p>
            </div>
            <div class="form-group">
                <input type="password" id="login_password" name="password" class="form-control" placeholder="Password">
                <p id="loginPasswordError" class="error-message text-danger"><?= htmlspecialchars($passwordError) ?></p>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>

<script>
function validateLoginForm(event) {
    event.preventDefault();  // Prevent the form from submitting

    let valid = true;
    document.getElementById('loginEmailError').textContent = '';
    document.getElementById('loginPasswordError').textContent = '';

    const loginEmail = document.getElementById('login_email').value.trim();
    const loginPassword = document.getElementById('login_password').value.trim();

    // Validate email format and check for leading spaces
    if (!loginEmail) {
        document.getElementById('loginEmailError').textContent = 'Email is required.';
        valid = false;
    } else if (/^\s/.test(loginEmail)) {  // Check for leading spaces
        document.getElementById('loginEmailError').textContent = 'Email cannot have leading spaces.';
        valid = false;
    } else if (!/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(loginEmail)) {
        document.getElementById('loginEmailError').textContent = 'Please enter a valid email address.';
        valid = false;
    }


    // Validate password
    if (!loginPassword) {
        document.getElementById('loginPasswordError').textContent = 'Password is required.';
        valid = false;
    }

    // If validation passes, check login credentials
    if (valid) {
        const formData = new FormData();
        formData.append('login', true);
        formData.append('email', loginEmail);
        formData.append('password', loginPassword);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                if (data.emailError) {
                    document.getElementById('loginEmailError').textContent = data.emailError;
                }
                if (data.passwordError) {
                    document.getElementById('loginPasswordError').textContent = data.passwordError;
                }

                // Reset the password field if login fails
                if (data.passwordError) {
                    document.getElementById('login_password').value = ''; // Reset password field
                }
            } else if (data.status === 'success') {
                // Redirect to dashboard after successful login
                window.location.href = data.redirect;  // Redirect to dashboard.php
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
    background-color: #d5deef;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-image: linear-gradient(135deg, #0f969c, #0c7075); 
}


.container {
    background-color:rgb(255, 255, 255);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 380px;
    text-align: center;
    border: 1px solid #072e33; 
}


h2 {
    font-size: 24px;
    font-weight: bold;
    color: #0f969c;
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
    background-color:rgb(230, 230, 230);
    color: #333;
}

.form-control:focus {
    border-color: #0f969c; 
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
    background-color: #0f969c; 
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #0c7075;
}


.toggle-form {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #333;
}

.toggle-form a {
    color: #0f969c;
    text-decoration: none;
    font-weight: bold;
}

.toggle-form a:hover {
    text-decoration: underline;
    color: #0c7075; 
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