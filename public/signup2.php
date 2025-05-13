<?php
require_once '../src/Auth.php';

header('Content-Type: application/json'); // Return JSON for AJAX

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cellphone = trim($_POST['cellphone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    

    // Check for empty fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($countryCode) || empty($cellphone) || empty($password) || empty($confirmPassword)) {
        $response['message'] = 'Please fill in all fields.';
    } elseif (!preg_match("/^\+\d{1,3}$/", $countryCode)) { 
        $response['message'] = 'Invalid country code.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        $response['message'] = 'Please enter a valid email address.';
    } elseif (Auth::isEmailExists($email)) { 
        $response['message'] = 'Email already exists.';
    } elseif ($password !== $confirmPassword) {
        $response['message'] = 'Passwords do not match.';
    } else {
        // If all validations pass, proceed with registration
        if (Auth::register($firstName, $lastName, $email, $countryCode, $cellphone, $password, $agreeToConditions)) {
            $response['status'] = 'success';
            $response['message'] = 'Signup successful. You can now log in.';
            $response['redirect'] = 'index.php'; 
        } else {
            $response['message'] = 'Error creating account.';
        }
    }

}

// Return the JSON response
echo json_encode($response);
?>
