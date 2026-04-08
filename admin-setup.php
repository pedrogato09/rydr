<?php
/**
 * Admin Setup & Test Page
 * This page helps diagnose and set up the admin login system
 */

require_once 'database/connection.php';

$results = [];
$hasErrors = false;

try {
    // Test 1: Database connection
    $results[] = array(
        'test' => 'Database Verbinding',
        'status' => 'success',
        'message' => 'Verbonden met database: car_rental'
    );

    // Test 2: Check user table
    $tableCheck = $conn->query("SHOW TABLES LIKE 'user'");
    if ($tableCheck->rowCount() > 0) {
        $results[] = array(
            'test' => 'User Tabel',
            'status' => 'success',
            'message' => 'User tabel exists'
        );
    } else {
        $results[] = array(
            'test' => 'User Tabel',
            'status' => 'error',
            'message' => 'User tabel niet gevonden!'
        );
        $hasErrors = true;
    }

    // Test 3: Check is_admin column
    $colCheck = $conn->query("SHOW COLUMNS FROM user LIKE 'is_admin'");
    if ($colCheck->rowCount() > 0) {
        $results[] = array(
            'test' => 'is_admin Kolom',
            'status' => 'success',
            'message' => 'is_admin kolom bestaat'
        );
    } else {
        $results[] = array(
            'test' => 'is_admin Kolom',
            'status' => 'warning',
            'message' => 'is_admin kolom niet gevonden - moet aangemaakt worden'
        );
    }

    // Test 4: Check admin users
    $adminCheck = $conn->query("SELECT COUNT(*) as count FROM user WHERE is_admin = 1");
    $adminCount = $adminCheck->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($adminCount > 0) {
        $adminUsers = $conn->query("SELECT id, naam, email FROM user WHERE is_admin = 1");
        $admins = $adminUsers->fetchAll(PDO::FETCH_ASSOC);
        $adminList = implode(', ', array_map(function($a) { return $a['email']; }, $admins));
        $results[] = array(
            'test' => 'Admin Gebruikers',
            'status' => 'success',
            'message' => "Gevonden: $adminList"
        );
    } else {
        $results[] = array(
            'test' => 'Admin Gebruikers',
            'status' => 'warning',
            'message' => 'Geen admin gebruikers gevonden'
        );
    }

} catch (PDOException $e) {
    $results[] = array(
        'test' => 'Database Test',
        'status' => 'error',
        'message' => 'Fout: ' . $e->getMessage()
    );
    $hasErrors = true;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Rydr</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <h1 class="text-3xl font-bold text-blue-600 mb-2">Rydr Admin Setup</h1>
            <p class="text-gray-600">Diagnostische tool voor admin login systeem</p>
        </div>

        <!-- Status Checks -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Systeemtest</h2>
            <div class="space-y-3">
                <?php foreach ($results as $result): ?>
                <div class="flex items-center gap-3 p-4 rounded-lg <?= $result['status'] === 'success' ? 'bg-green-50 border border-green-200' : ($result['status'] === 'warning' ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200') ?>">
                    <div class="font-bold text-xl">
                        <?= $result['status'] === 'success' ? '✓' : ($result['status'] === 'warning' ? '⚠' : '✗') ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900"><?= $result['test'] ?></p>
                        <p class="text-sm text-gray-600"><?= $result['message'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Setup Instructions -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Instellingen uitvoeren</h2>
            <p class="text-gray-700 mb-4">Klik op de knop hieronder om het admin systeem in te stellen:</p>
            <form method="POST" action="/setup-admin.php">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Admin Systeem Instellen →
                </button>
            </form>
        </div>

        <!-- Login Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-bold text-blue-900 mb-4">Demo Login Gegevens</h2>
            <div class="space-y-2 font-mono text-sm">
                <p><span class="font-bold">Email:</span> <code class="bg-white px-2 py-1 rounded">admin@morent.com</code></p>
                <p><span class="font-bold">Wachtwoord:</span> <code class="bg-white px-2 py-1 rounded">admin123</code></p>
            </div>
        </div>

        <!-- Login Button -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <p class="text-gray-600 mb-4">Klaar om in te loggen?</p>
            <a href="/pages/admin-login.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition">
                Naar Admin Login →
            </a>
        </div>

    </div>
</body>
</html>
