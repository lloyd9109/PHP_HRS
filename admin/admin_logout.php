<?php
session_start();
require_once 'admin.php';

$admin = new Admin($pdo);
$admin->logout();

header('Location: admin_login.php');
exit();
