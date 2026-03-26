<?php
// actions/register.php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_ARGON2ID); // Of PASSWORD_BCRYPT

    try {
        $stmt = $pdo->prepare("INSERT INTO account (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $password]);
        header("Location: /login");
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry voor email
            echo "Dit e-mailadres is al in gebruik.";
        } else {
            echo "Er ging iets mis.";
        }
    }
}