<?php
ob_start(); // Start output buffering to ensure headers can be sent

require_once '../src/Auth.php';
include 'header.php';
require_once '../config/database.php';

// Initialize variables
$passwordError = '';
$passwordSuccess = '';

// Fetch the current user data (Assuming you have a user session or similar mechanism)
$user_id = $_SESSION['user_id']; // Assuming session is active
$sql = "SELECT password_hash FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Function to validate password
function validatePassword($currentPassword, $newPassword, $confirmPassword, $userHash) {
    $errorMessages = [];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessages[] = "All fields are required.";
    } elseif (!password_verify($currentPassword, $userHash)) {
        $errorMessages[] = "Current password is incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessages[] = "New password and confirm password do not match.";
    } elseif (strlen($newPassword) < 8) {
        $errorMessages[] = "New password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $errorMessages[] = "New password must contain at least one uppercase letter and one number.";
    }

    return $errorMessages;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change-password'])) {
    $currentPassword = $_POST['current-password'];
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];
    
    // Get validation errors
    $validationErrors = validatePassword($currentPassword, $newPassword, $confirmPassword, $user['password_hash']);
    
    // If there are errors, show them
    if (count($validationErrors) > 0) {
        $passwordError = implode('<br>', $validationErrors);
    } else {
        // Proceed to hash the password and update
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password_hash' => $newPasswordHash, 'id' => $user_id]);
        
        $passwordSuccess = "Password successfully updated.";
        
        // Redirect to the same page
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit(); // Ensure no further code is executed after the redirect
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>
<body>
<div class="password-container">
    <form action="#" method="POST" id="change-password-form">
        <h2>Change Password</h2>
        <div class="password-group">
            <label for="current-password">Current Password</label>
            <input type="password" id="current-password" name="current-password">
            <span class="error-message"><?php echo htmlspecialchars($passwordError); ?></span>
        </div>
        <div class="password-group">
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" name="new-password">
            <span class="error-message"></span>
        </div>
        <div class="password-group">
            <label for="confirm-password">Confirm New Password</label>
            <input type="password" id="confirm-password" name="confirm-password">
            <span class="error-message"></span>
        </div>
        <?php if (!empty($passwordSuccess)) : ?>
            <p class="success-message"><?php echo htmlspecialchars($passwordSuccess); ?></p>
        <?php endif; ?>
        <div class="button-container">
            <button type="submit" name="change-password" class="submit-btn">Change Password</button>
        </div>
    </form>
 <button id="change-password-btn" class="change-password-btn" onclick="location.href='profile.php';">Go to Profile</button> <!-- Change Password Button -->
</div>

</body>
</html>

<footer>
    <?php include 'footer.php'; ?>
</footer>


<script>
document.getElementById("change-password-form").addEventListener("submit", function (e) {
    let valid = true;

    // Get field values
    const currentPassword = document.getElementById("current-password").value.trim();
    const newPassword = document.getElementById("new-password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();

    // Get error message elements
    const currentPasswordError = document.querySelector("#current-password + .error-message");
    const newPasswordError = document.querySelector("#new-password + .error-message");
    const confirmPasswordError = document.querySelector("#confirm-password + .error-message");

    // Clear previous error states
    currentPasswordError.classList.remove("visible");
    newPasswordError.classList.remove("visible");
    confirmPasswordError.classList.remove("visible");

    // Clear the input fields if there's a validation error
    function clearFields() {
        document.getElementById("current-password").value = '';
        document.getElementById("new-password").value = '';
        document.getElementById("confirm-password").value = '';
    }

    // Validate if fields are empty
    if (!currentPassword) {
        currentPasswordError.textContent = "Current password is required.";
        currentPasswordError.classList.add("visible");
        valid = false;
    }
    if (!newPassword) {
        newPasswordError.textContent = "New password is required.";
        newPasswordError.classList.add("visible");
        valid = false;
    }
    if (!confirmPassword) {
        confirmPasswordError.textContent = "Confirm password is required.";
        confirmPasswordError.classList.add("visible");
        valid = false;
    }

    // Validate new password format
    if (newPassword && newPassword.length < 8) {
        newPasswordError.textContent = "Password must be at least 8 characters long.";
        newPasswordError.classList.add("visible");
        valid = false;
    } else if (newPassword && (!/[A-Z]/.test(newPassword) || !/[0-9]/.test(newPassword))) {
        newPasswordError.textContent = "Password must contain at least one uppercase letter and one number.";
        newPasswordError.classList.add("visible");
        valid = false;
    }

    // Validate password match
    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        confirmPasswordError.textContent = "Passwords do not match.";
        confirmPasswordError.classList.add("visible");
        valid = false;
    }

    // If form validation is successful, check if the current password is correct via AJAX
    if (valid) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "validate_current_password.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Allow form submission
                    document.getElementById("change-password-form").submit();
                } else {
                    // Show error if current password is incorrect
                    currentPasswordError.textContent = response.message;
                    currentPasswordError.classList.add("visible");

                    // Clear the input fields upon error
                    clearFields();

                    e.preventDefault();
                }
            }
        };
        xhr.send("current-password=" + encodeURIComponent(currentPassword));
        e.preventDefault(); // Prevent form submission until AJAX request is finished
    } else {
        // Clear the input fields upon validation failure
        clearFields();
        e.preventDefault(); // Prevent form submission due to client-side validation errors
    }
});

</script>

<style>

/* Styling for Change Password Button */
.change-password-btn {
    background-color: #ff9800; /* Orange color */
    color: white;
    font-size: 1rem;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin: 10px 0; /* Add spacing around the button */
    text-transform: none; /* Ensure text is not capitalized */
}

.change-password-btn:hover {
    background-color: #e68a00; /* Darker orange on hover */
    transform: translateY(-2px); /* Lift effect on hover */
}

.change-password-btn:active {
    background-color: #cc7a00; /* Even darker orange when pressed */
    transform: translateY(0); /* Reset hover effect */
}
.profile-btn {
    background-color: #28a745;
    color: white;
    padding: 14px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 17px;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.profile-btn:hover {
    background-color: #218838;
    transform: scale(1.05);
}

.profile-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
    box-shadow: none;
}


.error-message {
    color: red;
    font-size: 0.9em;
    margin-top: 5px;
    display: block;
    min-height: 1.2em; /* Ensures consistent height for error messages */
    padding-left: 5px;
    visibility: hidden; /* Hides the message when no error */
}

.error-message:empty {
    display: block; /* Ensures the block occupies space even if empty */
}

.error-message.visible {
    visibility: visible; /* Use this class to make the error message visible */
}

/* Success Message */
.success-message {
    color: green;
    font-size: 14px;
    text-align: center;
    margin-top: 15px;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #fef9e7; 
    color: #333;
    margin: 0;
    padding: 0;
}

.password-container {
    background-color: #fff;
    padding: 40px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 800px;
    margin: 150px auto;
    transition: all 0.3s ease-in-out;
}

.password-container:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.password-container h2 {
    font-size: 26px;
    color: #333;
    text-align: center;
    margin-bottom: 25px;
    font-weight: bold;
}

.password-container .password-group {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.password-container .password-group label {
    font-size: 15px;
    color: #555;
    margin-bottom: 10px;
    font-weight: 600;
}

.password-container .password-group input {
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    width: 80%; 
    max-width: 400px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    margin: 0px;
    text-align: left;
    background-color: #f7f7f7;
}

.password-container .password-group input:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
}

.password-container .error-message {
    color: red;
    font-size: 13px;
    margin-top: 5px;
    padding-left: 5px;
}

/* Success Message */
.password-container .success-message {
    color: green;
    font-size: 14px;
    text-align: center;
    margin-top: 15px;
}

/* Button Container */
.password-container .button-container {
    text-align: center;
    margin-top: 25px;
}

.password-container .submit-btn {
    background-color: #007bff;
    color: white;
    padding: 14px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 17px;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.password-container .submit-btn:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

.password-container .submit-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
    box-shadow: none;
}

/* Responsive Design for Mobile */
@media (max-width: 600px) {
    .password-container {
        padding: 25px 15px;
    }

    .password-container .password-group input {
        width: 100%;
    }

    .password-container .submit-btn {
        width: 100%;
        font-size: 18px;
    }
}
</style>
