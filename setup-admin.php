<?php
/**
 * Database Setup Script for Admin Dashboard
 * Run this script once to initialize admin access
 */

require_once 'database/connection.php';

$errors = [];
$success = [];

try {
    $conn->beginTransaction();
    
    // Step 1: Add is_admin column if needed
    try {
        $checkColumn = $conn->query("SHOW COLUMNS FROM user LIKE 'is_admin'");
        if ($checkColumn->rowCount() === 0) {
            $conn->exec("ALTER TABLE user ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
            $success[] = "is_admin kolom aangemaakt";
        } else {
            $success[] = "is_admin kolom bestond al";
        }
    } catch (PDOException $e) {
        $errors[] = "Fout bij kolom controle: " . $e->getMessage();
    }

    // Step 2: Remove old admin records to recreate with proper hash
    try {
        $conn->exec("DELETE FROM user WHERE email = 'admin@morent.com'");
        $success[] = "Oude admin records verwijderd";
    } catch (PDOException $e) {
        $errors[] = "Fout bij verwijderen: " . $e->getMessage();
    }

    // Step 3: Create new admin user with proper password hashing
    if (empty($errors)) {
        try {
            $adminEmail = 'admin@morent.com';
            $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
            $adminName = 'Admin User';
            $adminPhone = '0612345678';
            
            $insert = $conn->prepare(
                "INSERT INTO user (naam, email, password, telefoonnummer, is_admin) 
                 VALUES (:naam, :email, :password, :telefoonnummer, :is_admin)"
            );
            
            $result = $insert->execute([
                ':naam' => $adminName,
                ':email' => $adminEmail,
                ':password' => $adminPassword,
                ':telefoonnummer' => $adminPhone,
                ':is_admin' => 1
            ]);
            
            if ($result) {
                $success[] = "Admin gebruiker succesvol aangemaakt";
            } else {
                $errors[] = "Fout bij het aanmaken van admin gebruiker";
            }
        } catch (PDOException $e) {
            $errors[] = "Database fout bij insert: " . $e->getMessage();
        }
    }

    // Step 4: Update existing test user to admin
    try {
        $update = $conn->prepare("UPDATE user SET is_admin = 1 WHERE email = :email");
        $update->execute([':email' => 'test@mail.com']);
        $success[] = "Test gebruiker bijgewerkt met admin rechten";
    } catch (PDOException $e) {
        $errors[] = "Fout bij test user update: " . $e->getMessage();
    }

    // Commit or rollback
    if (empty($errors)) {
        $conn->commit();
    } else {
        $conn->rollBack();
    }
    
} catch (Exception $e) {
    $errors[] = "Kritieke fout: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - MORENT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <h1 class="text-3xl font-bold text-blue-600 mb-2">MORENT Admin Setup</h1>
            <p class="text-gray-600">Installatie van admin authenticatie systeem</p>
        </div>

        <!-- Results -->
        <div class="space-y-6">
            
            <!-- Success Messages -->
            <?php if (!empty($success)): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h2 class="text-lg font-bold text-green-900 mb-4">✓ Succesvol</h2>
                <ul class="space-y-2">
                    <?php foreach ($success as $msg): ?>
                    <li class="flex items-start gap-2 text-green-800">
                        <span class="text-green-600 font-bold mt-0.5">→</span>
                        <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h2 class="text-lg font-bold text-red-900 mb-4">✗ Fouten</h2>
                <ul class="space-y-2">
                    <?php foreach ($errors as $msg): ?>
                    <li class="flex items-start gap-2 text-red-800">
                        <span class="text-red-600 font-bold mt-0.5">✗</span>
                        <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Demo Credentials -->
            <?php if (empty($errors)): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h2 class="text-lg font-bold text-blue-900 mb-4">🔐 Admin Login Gegevens</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-blue-700 font-semibold">Email:</p>
                        <code class="bg-white px-3 py-2 rounded border border-blue-200 text-sm font-mono">admin@morent.com</code>
                    </div>
                    <div>
                        <p class="text-sm text-blue-700 font-semibold">Wachtwoord:</p>
                        <code class="bg-white px-3 py-2 rounded border border-blue-200 text-sm font-mono">admin123</code>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <?php if (empty($errors)): ?>
                <a href="/pages/admin-login.php" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition text-center">
                    Naar Admin Login →
                </a>
                <?php else: ?>
                <button onclick="location.reload()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Opnieuw Proberen
                </button>
                <?php endif; ?>
                
                <a href="/admin-setup.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition text-center">
                    Terug
                </a>
            </div>
        </div>
    </div>
</body>
</html>
?>
