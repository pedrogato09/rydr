<?php
/**
 * Database Connection File
 * Handles all database operations for the car rental system
 */

$username = "root";
$password = ""; // XAMPP standaard wachtwoord
 
try {
    $conn = new PDO("mysql:host=localhost;dbname=car_rental;charset=utf8mb4", $username, $password);
    
    // Set error mode for better debugging
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e){
    // Log error instead of display in production
    error_log("Database Connection Error: " . $e->getMessage());
    
    // User-friendly error message
    die("<div style='font-family: Arial; padding: 20px; background: #fee; color: #c00; border: 1px solid #fcc; border-radius: 5px;'>"
        . "<h2>Database Verbinding Fout</h2>"
        . "<p>Kon niet verbinden met de database. Controleer alstublieft:</p>"
        . "<ul>"
        . "<li>MySQL/MariaDB draait</li>"
        . "<li>Database 'car_rental' bestaat</li>"
        . "<li>Credentials juist zijn</li>"
        . "</ul>"
        . "</div>");
}
 