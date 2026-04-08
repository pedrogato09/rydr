<?php
session_start();

// Als admin al ingelogd, redirect naar dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: /pages/admin-dashboard.php');
    exit;
}

$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   require_once $_SERVER['DOCUMENT_ROOT'] . '/database/connection.php';
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Vul alstublieft email en wachtwoord in';
    } else {
        try {
            // First, check if user exists
            $checkStmt = $conn->prepare("SELECT id, email, password, naam, is_admin FROM user WHERE email = :email LIMIT 1");
            $checkStmt->execute([':email' => $email]);
            $user = $checkStmt->fetch();
            
            if (!$user) {
                $error = 'Email niet gevonden in het systeem';
            } elseif (!isset($user['is_admin']) || (int)$user['is_admin'] !== 1) {
                $error = 'Dit account is niet geautoriseerd als admin';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Wachtwoord is onjuist';
            } else {
                // Succesvol ingelogd - regenerate session voor security
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_name'] = $user['naam'];
                
                header('Location: /pages/admin-dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Admin login error: ' . $e->getMessage());
            $error = 'Technische fout. Probeer later opnieuw';
        }
    }
}

// Check if setup is needed - maar load connection file slechts eenmaal
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($conn)) {
    try {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/database/connection.php';
        $checkSetup = $conn->query("SHOW COLUMNS FROM user LIKE 'is_admin'");
        if ($checkSetup->rowCount() === 0) {
            $info = 'Systeem moet nog ingesteld worden. Ga naar <strong>/admin-setup.php</strong> om het in te stellen.';
        }
    } catch (Exception $e) {
        // Verbinding OK of fout - niet kritisch
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Rydr</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-blue-600 mb-2">MORENT</h1>
                <p class="text-gray-500">Admin Dashboard</p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Info Message (Setup) -->
            <?php if ($info): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm">
                ℹ️ <?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email adres
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        placeholder="admin@morent.com"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Wachtwoord
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200 transform hover:scale-105 active:scale-95"
                >
                    Inloggen
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-900 mt-6">
                <p class="font-semibold mb-3">🔐 Admin Inloggegevens:</p>
                <div class="space-y-2">
                    <p>
                        <span class="font-semibold text-blue-800">Email:</span><br>
                        <code class="bg-white px-3 py-1 rounded text-xs font-mono">admin@morent.com</code>
                    </p>
                    <p>
                        <span class="font-semibold text-blue-800">Wachtwoord:</span><br>
                        <code class="bg-white px-3 py-1 rounded text-xs font-mono">admin123</code>
                    </p>
                </div>
                <p class="text-xs text-blue-700 mt-3 italic">⚠️ Let op: Eerst moet je /admin-setup.php uitvoeren!</p>
            </div>
        </div>
    </div>
</body>
</html>
