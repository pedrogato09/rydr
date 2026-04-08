<?php
// Voeg hier je database connectie toe (bijv. require_once '../includes/db.php';)
// Voorbeeld PDO connectie op basis van jouw SQL dump:
$host = '127.0.0.1';
$db   = 'car_rental';
$user = 'root';
$pass = ''; // vul je XAMPP wachtwoord in, standaard leeg

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Haal recente transacties op met JOINs over payment, reservation, car en category
$stmt = $pdo->query("
    SELECT c.model, c.brand, c.image, cat.naam AS category, p.datum, p.bedrag 
    FROM payment p
    JOIN reservation r ON p.reservation_id = r.id
    JOIN car c ON r.car_id = c.car_id
    JOIN category cat ON c.category_id = cat.id
    ORDER BY p.datum DESC 
    LIMIT 4
");
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rydr - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 flex h-screen font-sans">

    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <div class="h-20 flex items-center px-8">
            <h1 class="text-2xl font-bold text-blue-600">Rydr</h1>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
            <p class="text-xs text-gray-400 font-semibold px-4 mb-4">MAIN MENU</p>
            <a href="/dashboard" class="flex items-center gap-3 bg-blue-600 text-white px-4 py-3 rounded-xl">
                <span>🏠</span> Dashboard
            </a>
            <a href="/car-rent" class="flex items-center gap-3 text-gray-500 hover:text-blue-600 px-4 py-3">
                <span>🚗</span> Car Rent
            </a>
            </nav>
        <div class="p-4 border-t border-gray-200">
            <a href="/logout" class="flex items-center gap-3 text-gray-500 hover:text-red-600 px-4 py-2">
                <span>🚪</span> Log Out
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8">
            <div class="relative w-96">
                <input type="text" placeholder="Search something here" class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-200 focus:outline-none focus:border-blue-500">
                <span class="absolute left-4 top-2.5 text-gray-400">🔍</span>
            </div>
            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500">❤️</button>
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 relative">
                    🔔 <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500">⚙️</button>
                <img src="https://i.pravatar.cc/150" alt="Profile" class="w-10 h-10 rounded-full">
            </div>
        </header>

        <div class="p-8 overflow-y-auto flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold mb-4">Details Rental</h2>
                    <div class="bg-blue-50 w-full h-64 rounded-xl mb-6 flex items-center justify-center">
                        <span class="text-gray-400">Map Integration (Google Maps/Leaflet)</span>
                    </div>
                    </div>

                <div class="space-y-8">
                    
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold">Top 5 Car Rental</h2>
                            <button class="text-gray-400">•••</button>
                        </div>
                        <div class="relative h-48 w-full flex justify-center">
                            <canvas id="rentalChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-bold">Recent Transaction</h2>
                            <a href="#" class="text-blue-600 text-sm font-semibold">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($recentTransactions as $transaction): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-24 h-16 bg-gray-100 rounded-lg flex items-center justify-center p-2">
                                        <img src="/assets/images/<?= htmlspecialchars($transaction['image']) ?>" alt="<?= htmlspecialchars($transaction['model']) ?>" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div>
                                        <h3 class="font-bold"><?= htmlspecialchars($transaction['brand'] . ' ' . $transaction['model']) ?></h3>
                                        <p class="text-sm text-gray-400"><?= htmlspecialchars($transaction['category']) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-400"><?= date('d F', strtotime($transaction['datum'])) ?></p>
                                    <p class="font-bold text-lg">$<?= number_format($transaction['bedrag'], 2) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('rentalChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Sport Car', 'SUV', 'Coupe', 'Hatchback', 'MPV'],
                datasets: [{
                    data: [17439, 9478, 18197, 12510, 14406],
                    backgroundColor: ['#1e3a8a', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                plugins: {
                    legend: { display: false } // Verbergt standaard legenda zodat je hem zelf in HTML kan maken
                }
            }
        });
    </script>
</body>
</html>