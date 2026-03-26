<?php
// actions/login.php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Gebruik een Prepared Statement om SQL-injectie te voorkomen
    $stmt = $pdo->prepare("SELECT * FROM account WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Wachtwoord is correct (bijv. voor kelvin@kelvin.nl)
        session_start();
        $_SERVER['user_id'] = $user['id'];
        header("Location: /home");
        exit;
    } else {
        echo "Onjuiste inloggegevens.";
    }
}