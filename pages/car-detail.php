<?php require "includes/header.php"; ?>
<?php require "database/connection.php"; ?>


<?php
// ID uit URL halen
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Geen auto gekozen";
    exit;
}

// Auto ophalen
$stmt = $conn->prepare("SELECT * FROM Car WHERE car_id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();

if (!$car) {
    echo "Auto niet gevonden";
    exit;
}
?>

<main class="car-detail">
    <div class="grid">

        <div class="row">
            <div class="advertorial">
                <h2><?php echo $car['brand'] . " " . $car['model']; ?></h2>
                <p>Perfecte auto voor comfort en snelheid.</p>

                <img src="assets/images/products/<?php echo $car['image']; ?>" alt="">
            </div>
        </div>

        <div class="row white-background">
            <h3><?php echo $car['brand'] . " " . $car['model']; ?></h3>

            <div class="rating">
                <span class="stars stars-4"></span>
                <span>Reviews</span>
            </div>

            <p>Deze auto is beschikbaar voor verhuur.</p>

            <div class="car-type">
                <div class="grid">

                    <div class="row">
                        <span class="accent-color">Type Car</span>
                        <span><?php echo ($car['category_id'] == 1) ? 'Sport' : 'SUV'; ?></span>
                    </div>

                    <div class="row">
                        <span class="accent-color">Capacity</span>
                        <span><?php echo $car['aantal_personen']; ?> personen</span>
                    </div>

                </div>

                <div class="grid">
                    <div class="row">
                        <span class="accent-color">Steering</span>
                        <span>Manual</span>
                    </div>

                    <div class="row">
                        <span class="accent-color">Gasoline</span>
                        <span>-</span>
                    </div>
                </div>

                <div class="call-to-action">
                    <div class="row">
                        <span class="font-weight-bold">
                            €<?php echo $car['price_per_day']; ?>
                        </span> / dag
                    </div>

                    <div class="row">
                        <a href="#" class="button-primary">Huur nu</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</main>

<?php require "includes/footer.php"; ?>