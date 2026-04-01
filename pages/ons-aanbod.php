<?php require "includes/header.php"; require "database/connection.php"; ?>

<main>
    <div class="filter-section">
        <aside class="sidebar">
            <form id="main-filter-form" action="" method="GET" class="filter-form">
                <h3>TYPE</h3>
                <div class="filter-group">
                <?php
                $categoryStmt = $conn->query("SELECT id, naam FROM category ORDER BY naam");
                $categoryOptions = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($categoryOptions as $category) {
                    $catId = $category['id'];
                    $catName = $category['naam'];
                    $checked = isset($_GET['category']) && in_array((string)$catId, $_GET['category']) ? 'checked' : '';
                    echo "<label class='checkbox-option'><input type='checkbox' name='category[]' value='" . htmlspecialchars($catId) . "' $checked> " . htmlspecialchars($catName) . "</label>";
                }
                ?>
                </div>

                <h3>LOCATIE (Pickup)</h3>
                <select name="location">
                    <option value="">Alle locaties</option>
                    <?php
                    try {
                        $locations = $conn->query("SELECT DISTINCT locatie FROM car WHERE locatie IS NOT NULL AND locatie != ''")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($locations as $location) {
                            $sel = (isset($_GET['location']) && $_GET['location'] === $location) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($location) . "' $sel>" . htmlspecialchars($location) . "</option>";
                        }
                    } catch (PDOException $e) {
                        // Locatie kolom bestaat niet, toon geen opties
                        echo "<option disabled>Locatie niet beschikbaar</option>";
                    }
                    ?>
                </select>

                <h3>CAPACITEIT</h3>
                <?php
                $capaciteitOpties = [2, 4, 6, 8, '8+'];
                foreach ($capaciteitOpties as $capOpt) {
                    $checked = isset($_GET['capacity']) && in_array((string)$capOpt, $_GET['capacity']) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='capacity[]' value='" . htmlspecialchars($capOpt) . "' $checked> " . htmlspecialchars($capOpt) . " Personen</label>";
                }
                ?>

                <h3>PRIJS PER DAG</h3>
                <input type="range" name="max_price" min="0" max="300" value="<?php echo isset($_GET['max_price']) ? (int)$_GET['max_price'] : 300; ?>" oninput="this.nextElementSibling.textContent='€'+this.value">
                <span class="price-label">€<?php echo isset($_GET['max_price']) ? (int)$_GET['max_price'] : 300; ?></span>

                <button type="submit" class="button-primary">Filteren</button>
                <a href="ons-aanbod" style="margin-left: 8px;">Reset</a>
            </form>
        </aside>

        <section class="cars">
                <?php
                // Begin met alle beschikbare auto's en voeg filtercriteria dynamisch toe
                $sql = "SELECT c.*, cat.naam AS category_name FROM car c LEFT JOIN category cat ON c.category_id = cat.id WHERE c.beschikbaar = 1";
            $params = [];

            if (!empty($_GET['category'])) {
                $categoryIds = array_map('intval', $_GET['category']);
                $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
                $sql .= " AND c.category_id IN ($placeholders)";
                foreach ($categoryIds as $catId) {
                    $params[] = $catId;
                }
            }

            if (!empty($_GET['capacity'])) {
                $capacities = $_GET['capacity'];
                $exactCaps = [];
                $has8plus = false;

                foreach ($capacities as $cap) {
                    if ($cap === '8+') {
                        $has8plus = true;
                        continue;
                    }
                    $exactCaps[] = (int) $cap;
                }

                if (!empty($exactCaps)) {
                    $placeholders = implode(',', array_fill(0, count($exactCaps), '?'));
                    $sql .= " AND c.aantal_personen IN ($placeholders)";
                    foreach ($exactCaps as $cap) {
                        $params[] = $cap;
                    }
                }

                if ($has8plus) {
                    $sql .= " AND c.aantal_personen >= 8";
                }
            }

            if (!empty($_GET['location'])) {
                try {
                    $conn->query("SELECT locatie FROM car LIMIT 1"); // Check if column exists
                    $sql .= " AND c.locatie = ?";
                    $params[] = $_GET['location'];
                } catch (PDOException $e) {
                    // Locatie kolom bestaat niet, negeer filter
                }
            }

            if (!empty($_GET['max_price'])) {
                $sql .= " AND c.price_per_day <= ?";
                $params[] = $_GET['max_price'];
            }

            if (!empty($_GET['search'])) {
                $searchTerm = '%' . $_GET['search'] . '%';
                $sql .= " AND (c.brand LIKE ? OR c.model LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$cars) {
                echo "<p>Geen auto's gevonden met deze filters. Probeer de filters te verwijderen.</p>";
            }

            foreach ($cars as $car) {
                echo '<article class="car-details">';
                echo '<div class="car-brand"><h3>' . htmlspecialchars($car['brand']) . ' ' . htmlspecialchars($car['model']) . '</h3><div class="car-type">' . htmlspecialchars($car['category_name'] ?? 'Onbekend') . '</div></div>';
                echo '<img src="assets/images/products/' . htmlspecialchars($car['image']) . '" alt="' . htmlspecialchars($car['brand']) . ' ' . htmlspecialchars($car['model']) . '">';
                echo '<div class="car-specification"><span><img src="assets/images/icons/gas-station.webp" alt="Fuel"> ' . htmlspecialchars($car['bouwjaar']) . '</span><span><img src="assets/images/icons/car.webp" alt="Transmission"> Manual</span><span><img src="assets/images/icons/profile-2user.webp" alt="Persons"> ' . htmlspecialchars($car['aantal_personen']) . ' Personen</span></div>';
                echo '<div class="rent-details"><span><span class="font-weight-bold">€' . htmlspecialchars($car['price_per_day']) . '</span> / dag</span><a href="car-detail?id=' . (int)$car['car_id'] . '" class="button-primary">Bekijk nu</a></div>';
                if (isset($car['locatie']) && !empty($car['locatie'])) {
                    echo '<p style="margin: 0.5rem 0 0; color: #90A3BF;">Pickup locatie: ' . htmlspecialchars($car['locatie']) . '</p>';
                }
                echo '<p style="margin:0.5rem 0; color:#555;">Max. prijs: €' . htmlspecialchars(number_format($car['price_per_day'], 2, ',', '.')) . ' - voor ' . htmlspecialchars($car['aantal_personen']) . ' personen</p>';
                echo '</article>';
            }
            ?>
        </section>
    </div>
</main>

<?php require "includes/footer.php"; ?>