<div class="filter-container">
    <div class="filter-card">
        <div class="radio-group">
            <input type="radio" checked> <label>Pick – Up</label>
        </div>
        <div class="inputs-row">
            <div class="input-group">
                <label>Locations</label>
                <select><option>Select your city</option></select>
            </div>
            <div class="input-group">
                <label>Date</label>
                <input type="date" placeholder="Select your date">
            </div>
            <div class="input-group">
                <label>Time</label>
                <input type="time" placeholder="Select your time">
            </div>
        </div>
    </div>

    <div class="swap-button">
        <i class="fas fa-arrows-alt-v"></i> </div>

    <div class="filter-card">
        <div class="radio-group">
            <input type="radio" checked> <label>Drop – Off</label>
        </div>
        <div class="inputs-row">
            <div class="input-group">
                <label>Locations</label>
                <select><option>Select your city</option></select>
            </div>
            <div class="input-group">
                <label>Date</label>
                <input type="date" placeholder="Select your date">
            </div>
            <div class="input-group">
                <label>Time</label>
                <input type="time" placeholder="Select your time">
            </div>
        </div>
    </div>
    
    <button class="search-btn">🔍</button>
</div>




<?php require "includes/header.php"; ?>
<?php require "database/connection.php"; ?>


<?php
$stmt = $conn->prepare("SELECT * FROM Car");
$stmt->execute();
$cars = $stmt->fetchAll();
?>

<header>
    <div class="advertorials">
        <div class="advertorial">
            <h2>Hét platform om een auto te huren</h2>
            <p>Snel en eenvoudig een auto huren. Natuurlijk voor een lage prijs.</p>
            <a href="#" class="button-primary">Huur nu een auto</a>
            <img src="assets/images/car-rent-header-image-1.webp" alt="">
        </div>

        <div class="advertorial">
            <h2>Wij verhuren ook bedrijfswagens</h2>
            <p>Voor een vaste lage prijs met prettig voordelen.</p>
            <a href="#" class="button-primary">Huur een bedrijfswagen</a>
            <img src="assets/images/car-rent-header-image-2.webp" alt="">
        </div>
    </div>
</header>

<main>

<h2 class="section-title">Populaire auto's</h2>

<div class="cars">
    <?php foreach ($cars as $car): ?>
        <div class="car-details">

            <div class="car-brand">
                <h3><?php echo $car['brand']; ?></h3>
                <div class="car-type">
                    <?php echo ($car['category_id'] == 1) ? 'Sport' : 'SUV'; ?>
                </div>
            </div>

            <img src="assets/images/products/<?php echo $car['image']; ?>" alt="">

            <div class="car-specification">
                <span>
                    <img src="assets/images/icons/profile-2user.svg" alt="">
                    <?php echo $car['aantal_personen']; ?> Personen
                </span>
            </div>

            <div class="rent-details">
                <span>
                    <span class="font-weight-bold">
                        €<?php echo $car['price_per_day']; ?>
                    </span> / dag
                </span>

                <!-- BELANGRIJKE LINK -->
                <a href="car-detail?id=<?php echo $car['car_id']; ?>" class="button-primary">
                    Bekijk nu
                </a>
            </div>

        </div>
    <?php endforeach; ?>
</div>

</main>

<?php require "includes/footer.php"; ?>

