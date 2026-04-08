<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /pages/admin-login.php');
    exit;
}

require_once '../database/connection.php';

// Initialize variables with defaults
$totalCars = 0;
$availableCars = 0;
$totalReservations = 0;
$totalRevenue = 0;
$topCars = [];
$recentTransactions = [];
$revenueByCategory = [];
$dbError = false;

try {
    // Statistieken ophalen
    $totalCars = (int)($conn->query("SELECT COUNT(*) FROM car")->fetch(PDO::FETCH_COLUMN) ?? 0);
    $availableCars = (int)($conn->query("SELECT COUNT(*) FROM car WHERE beschikbaar = 1")->fetch(PDO::FETCH_COLUMN) ?? 0);
    $totalReservations = (int)($conn->query("SELECT COUNT(*) FROM reservation")->fetch(PDO::FETCH_COLUMN) ?? 0);
    $totalRevenue = (float)($conn->query("SELECT COALESCE(SUM(bedrag), 0) FROM payment WHERE status = 'betaald'")->fetch(PDO::FETCH_COLUMN) ?? 0);
    
    // Top rental cars
    $stmt = $conn->prepare("
        SELECT c.car_id, c.brand, c.model, COALESCE(cat.naam, 'Onbekend') as category, COUNT(r.id) as rentals
        FROM car c
        LEFT JOIN category cat ON c.category_id = cat.id
        LEFT JOIN reservation r ON c.car_id = r.car_id
        GROUP BY c.car_id, c.brand, c.model, cat.naam
        ORDER BY rentals DESC
        LIMIT 5
    ");
    $stmt->execute();
    $topCars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent transactions - met default waarden
    $stmt = $conn->prepare("
        SELECT c.model, c.brand, COALESCE(c.image, 'default.webp') as image, 
               COALESCE(cat.naam, 'Overig') AS category, p.datum, p.bedrag 
        FROM payment p
        JOIN reservation r ON p.reservation_id = r.id
        JOIN car c ON r.car_id = c.car_id
        LEFT JOIN category cat ON c.category_id = cat.id
        ORDER BY p.datum DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenue data by category
    $stmt = $conn->prepare("
        SELECT COALESCE(cat.naam, 'Overig') as naam, COALESCE(SUM(p.bedrag), 0) as total
        FROM payment p
        JOIN reservation r ON p.reservation_id = r.id
        JOIN car c ON r.car_id = c.car_id
        LEFT JOIN category cat ON c.category_id = cat.id
        WHERE p.status = 'betaald'
        GROUP BY cat.id
    ");
    $stmt->execute();
    $revenueByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $dbError = true;
    error_log('Admin dashboard error: ' . $e->getMessage());
}

// Prepare data for charts with safety checks
$categoryLabels = json_encode(count($topCars) > 0 ? array_column($topCars, 'model') : []);
$categoryData = json_encode(count($topCars) > 0 ? array_column($topCars, 'rentals') : []);
$revenueCategoryLabels = json_encode(count($revenueByCategory) > 0 ? array_column($revenueByCategory, 'naam') : []);
$revenueCategoryData = json_encode(count($revenueByCategory) > 0 ? array_column($revenueByCategory, 'total') : []);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Rydr</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 flex h-screen font-sans">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-sm">
        <div class="h-20 flex items-center px-8 border-b border-gray-100">
            <h1 class="text-2xl font-bold text-blue-600">Rydr</h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <p class="text-xs text-gray-400 font-semibold px-4 mb-6 uppercase tracking-wider">Hauptmenu</p>
            
            <a href="/pages/admin-dashboard.php" class="flex items-center gap-3 bg-blue-600 text-white px-4 py-3 rounded-xl font-medium transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                Dashboard
            </a>
            
            <button class="flex items-center gap-3 text-gray-600 hover:text-blue-600 px-4 py-3 rounded-xl font-medium transition w-full text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Auto´s
            </button>
            
            <button class="flex items-center gap-3 text-gray-600 hover:text-blue-600 px-4 py-3 rounded-xl font-medium transition w-full text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2m0 0l2-2m-2 2v-6m0 6H9m0 0h2"></path></svg>
                Reserveringen
            </button>
            
            <button class="flex items-center gap-3 text-gray-600 hover:text-blue-600 px-4 py-3 rounded-xl font-medium transition w-full text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                inkomen
            </button>
            
            <button class="flex items-center gap-3 text-gray-600 hover:text-blue-600 px-4 py-3 rounded-xl font-medium transition w-full text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Klanten
            </button>
        </nav>
        
        <div class="p-4 border-t border-gray-100 space-y-2">
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-xl">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></p>
                    <p class="text-xs text-gray-500 truncate">Administrator</p>
                </div>
            </div>
            <a href="/pages/admin-logout.php" class="flex items-center gap-3 text-gray-600 hover:text-red-600 px-4 py-2 rounded-xl font-medium transition w-full text-left">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                uitloggen
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Header -->
        <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 shadow-sm">
            <div class="relative w-96">
                <input type="text" placeholder="Search something here" class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.172 16.172a4 4 0 015.656 0M9 10a4 4 0 118 0 4 4 0 01-8 0zm0 0a4 4 0 118 0 4 4 0 01-8 0z"></path></svg>
                </button>
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 1115 15.414v1.586a2.068 2.068 0 00-2.068 2.068A2.068 2.068 0 0015 21h4"></path></svg>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                </button>
                <img src="https://i.pravatar.cc/150?u=<?= urlencode($_SESSION['admin_email'] ?? '') ?>" alt="Profile" class="w-10 h-10 rounded-full border border-gray-200">
            </div>
        </header>

        <!-- Content Area -->
        <div class="p-8 overflow-y-auto flex-1">
            
            <?php if ($dbError): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-3">
                <span class="text-xl">⚠️</span>
                <div>
                    <p class="font-semibold">Databaseverbinding probleem</p>
                    <p class="text-sm mt-1">Sommige gegevens zijn mogelijk niet beschikbaar. Probeer later opnieuw.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Welkom terug, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-gray-500 mt-1">Hier is een overzicht van uw beheerstatistieken</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Cars -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Totaal aantal auto's</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?= $totalCars ?></p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Available Cars -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Beschikbare auto's</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?= $availableCars ?></p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Total Reservations -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Reserveringen</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?= $totalReservations ?></p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Totaal inkomen</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">$<?= number_format($totalRevenue, 2) ?></p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                
                <!-- Top 5 Cars Chart -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900">Top 5 best verkochte Autos</h2>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                        </button>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="topCarsChart"></canvas>
                    </div>
                </div>

                <!-- Rental Status Donut Chart -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900">Mietstatus</h2>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                        </button>
                    </div>
                    <div class="relative h-64 w-full flex justify-center items-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-6 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-blue-600"></div>
                                <span class="text-gray-600">Beschikbaar</span>
                            </div>
                            <span class="font-semibold text-gray-900"><?= $availableCars ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <span class="text-gray-600">Verhuurd</span>
                            </div>
                            <span class="font-semibold text-gray-900"><?= $totalCars - $availableCars ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Laatste Transacties</h2>
                    <a href="#" class="text-blue-600 text-sm font-semibold hover:text-blue-700">Alle tonen →</a>
                </div>
                <div class="space-y-4">
                    <?php if (count($recentTransactions) > 0): ?>
                        <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="flex items-center justify-between p-4 hover:bg-gray-50 rounded-xl transition">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-16 h-12 bg-gray-100 rounded-lg flex items-center justify-center p-2 flex-shrink-0">
                                    <img src="/assets/images/<?= htmlspecialchars($transaction['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($transaction['model'], ENT_QUOTES, 'UTF-8') ?>" class="max-w-full max-h-full object-contain">
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900"><?= htmlspecialchars($transaction['brand'] . ' ' . $transaction['model'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($transaction['category'], ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500"><?= date('d. M Y', strtotime($transaction['datum'])) ?></p>
                                <p class="font-bold text-gray-900">$<?= number_format($transaction['bedrag'], 2) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Geen transacties gevonden</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Top 5 Cars Chart
        const ctxTopCars = document.getElementById('topCarsChart').getContext('2d');
        new Chart(ctxTopCars, {
            type: 'bar',
            data: {
                labels: <?= $categoryLabels ?>,
                datasets: [{
                    label: 'Mieten',
                    data: <?= $categoryData ?>,
                    backgroundColor: '#3b82f6',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Status Chart (Doughnut)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Verfügbar', 'Vermietet'],
                datasets: [{
                    data: [<?= $availableCars ?>, <?= $totalCars - $availableCars ?>],
                    backgroundColor: ['#3b82f6', '#ef4444'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>
