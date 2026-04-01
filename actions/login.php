<?php
// actions/login.php
require_once __DIR__ . '/../config/db.php';

$select_user = $conn->prepare("SELECT * FROM account WHERE email = :email");
$select_user->bindParam(":email", $_POST['email']);
$select_user->execute();
$user = $select_user->fetch(PDO::FETCH_ASSOC);

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
