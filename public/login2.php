<?php
require_once '../config/database.php';
require_once '../src/Auth.php';

header('Content-Type: application/json'); // Return JSON for AJAX

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate fields
    if (empty($email) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Comprehensive email validation
        $response['message'] = 'Please enter a valid email address.';
    } elseif (!Auth::isEmailExists($email)) {
        $response['message'] = 'The email you entered isn’t connected to an account.';
    } elseif (!Auth::login($email, $password)) {
        $response['message'] = 'The password you’ve entered is incorrect.';
    } else {
            header('Location: index.php');
            exit; // Don't forget to call exit after the redirect
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
