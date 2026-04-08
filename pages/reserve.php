<?php require "includes/header.php"; ?>
<?php require "database/connection.php"; ?>

<?php
$car_id = $_GET['id'] ?? null;
if (!$car_id) die("Geen auto gekozen");

$stmt = $conn->prepare("SELECT * FROM Car WHERE car_id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

if (!isset($_SESSION['user_id'])) {
    die("❌ Je moet eerst inloggen");
}
$user_id = $_SESSION['user_id'];

    $start = $_POST['start_datum'];
    $end = $_POST['eind_datum'];
    $pickup = $_POST['pickup_locatie'];
    $drop = $_POST['dropoff_locatie'];
    $pickup_tijd = $_POST['pickup_tijd'];
    $drop_tijd = $_POST['dropoff_tijd'];

    // ❗ DEZE MIS JE
    $payment_method = $_POST['payment_method'];

    // ❗ PRIJS BEREKENEN
    $days = (strtotime($end) - strtotime($start)) / (60*60*24);

    if ($days <= 0) {
        echo "❌ Ongeldige datum";
    } else {

        $total = $days * $car['price_per_day'];

        // ✅ 1. RESERVATION OPSLAAN
        $stmt = $conn->prepare("
            INSERT INTO Reservation 
            (user_id, car_id, start_datum, eind_datum, totaal_prijs, status,
             pickup_locatie, dropoff_locatie, pickup_tijd, dropoff_tijd)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $car_id,
            $start,
            $end,
            $total,
            "actief",
            $pickup,
            $drop,
            $pickup_tijd,
            $drop_tijd
        ]);

        // ✅ 2. ID PAKKEN
        $reservation_id = $conn->lastInsertId();

        // ✅ 3. PAYMENT OPSLAAN
        $stmt = $conn->prepare("
            INSERT INTO Payment 
            (reservation_id, bedrag, betaalmethode, status, datum)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $reservation_id,
            $total,
            $payment_method,
            "betaald"
        ]);

        echo "✅ All done!";
    }
}

?>

<main>

<div class="reserve-container">

    <!-- LINKS -->
    <div>

        <div class="reserve-box">
            <h2>Billing Info</h2>

            <div class="form-grid">
                <input type="text" placeholder="Name">
                <input type="text" placeholder="Phone">
                <input type="text" placeholder="Address" class="full-width">
            </div>
        </div>

        <form method="POST">

        <div class="reserve-box">
            <h2>Rental Info</h2>

            <div class="form-grid">
                <input type="text" name="pickup_locatie" placeholder="Pick-up locatie">
                <input type="date" name="start_datum">

                <input type="time" name="pickup_tijd">
                <input type="text" name="dropoff_locatie" placeholder="Drop-off locatie">

                <input type="date" name="eind_datum">
                <input type="time" name="dropoff_tijd">
            </div>
        </div>

       <div class="payment-option">
    <label>
        <input type="radio" name="payment_method" value="Credit Card" required>
        Credit Card
    </label>
</div>

<div class="payment-option">
    <label>
        <input type="radio" name="payment_method" value="PayPal">
        PayPal
    </label>
</div>

<div class="payment-option">
    <label>
        <input type="radio" name="payment_method" value="Bitcoin">
        Bitcoin
    </label>
</div>
        <div class="reserve-box">
            <h2>Confirmation</h2>

            <label>
                <input type="checkbox"> Ik ga akkoord met voorwaarden
            </label>

            <br>

            <button class="button-primary" type="submit">
                Rent Now
            </button>

            <p><?php echo $message; ?></p>
        </div>

        </form>

    </div>

    <!-- RECHTS -->
   <div class="grid">

        <div class="row">
            <div class="advertorial">
                <h2><?php echo $car['brand'] . " " . $car['model']; ?></h2>

                <img src="assets/images/products/<?php echo $car['image']; ?>" alt="">
            </div>
        </div>

        <div class="reserve-row-white-background">
            <h3><?php echo $car['brand'] . " " . $car['model']; ?></h3>

            <div class="rating">
                <span class="stars stars-4"></span>
                <span  style="font-size: 14px;">440+ Reviews</span>
            </div>
<div class = "car-subtotal">
    <p >subtotal: €<?php echo $car['price_per_day']  ?></p>
    <p>Tax: €0</p>

    <h5>Tota Remtal Price <h5>
    <h3>€<?php echo $car['price_per_day']  ?></h3> 
    </div>

</div>

</main>

<?php require "includes/footer.php"; ?>