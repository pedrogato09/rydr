<?php
session_start();
require_once "database/connection.php";

// Valideer POST data
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email en wachtwoord zijn vereist';
    header('Location: /login-form');
    exit;
}

try {
    // Prepareer en voer query uit
    $select_user = $conn->prepare("SELECT id, email, password FROM user WHERE email = :email");
    $select_user->execute([':email' => $email]);
    $user = $select_user->fetch(PDO::FETCH_ASSOC);
    
    // Controleer of gebruiker bestaat en wachtwoord juist is
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        
        // Prevent session fixation attack
        session_regenerate_id(true);
        
        header('Location: /');
        exit;
    } else {
        $_SESSION['error'] = 'Email of wachtwoord onjuist';
        header('Location: /login-form');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Technische fout. Probeer later opnieuw';
    error_log('Login error: ' . $e->getMessage());
    header('Location: /login-form');
    exit;
}