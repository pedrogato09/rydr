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
            <h2>The Best Platform for Car Rental</h2>
            <p>Ease of doing a car rental safely and reliably. Of course at low prices.</p>
<a href="ons-aanbod" class="button-primary">Rental Car</a>         
   <img src="assets/images/car-rent-header-image-1.webp" alt="">
        </div>

        <div class="advertorial">
            <h2>Easy way to rent a car at a low price</h2>
            <p>Providing cheap and reliable car rental services.</p>
            <a href="ons-aanbod" class="button-primary">Rental Car</a>
            <img src="assets/images/car-rent-header-image-2.webp" alt="">
        </div>
    </div>
</header>

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
    
  
</div>


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
        <img src="assets/images/icons/gas-station.webp" alt="">
        90L
    </span>
    
    <span>
        <img src="assets/images/icons/car.webp" alt="">
        Manual
    </span>
    <span>
        <img src="assets/images/icons/profile-2user.webp" alt="">
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
                    Rental Now
                </a>
            </div>

        </div>
    <?php endforeach; ?>
     <div class="show-more">
<a href="ons-aanbod" class="button-primary">Show More</a></div>

</main>

<?php require "includes/footer.php"; ?>

