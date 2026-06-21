<?php
// booking.php
require_once 'includes/config.php';

$success = false;
$error = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = true;
}

if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>

<style>
    /* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Georgia', serif;
}

body {
    background-color: #0a0a0a;
    color: #ffffff;
}

/* COLORS */
:root {
    --black: #0a0a0a;
    --gold: #c9a24e;
    --white: #ffffff;
    --gray: #aaa;
}

/* NAVBAR */
header {
    padding: 20px 50px;
    position: fixed;
    width: 100%;
    top: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
}

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    color: var(--gold);
    letter-spacing: 2px;
}

nav ul {
    display: flex;
    list-style: none;
    gap: 30px;
}

nav a {
    text-decoration: none;
    color: var(--white);
    transition: 0.3s;
}

nav a:hover {
    color: var(--gold);
}

/* BUTTON */
.btn {
    display: inline-block;
    padding: 12px 25px;
    border: 1px solid var(--gold);
    color: var(--gold);
    text-decoration: none;
    transition: 0.3s;
}

.btn:hover {
    background: var(--gold);
    color: black;
}

.booking {
    padding: 120px 50px;
    text-align: center;
}

.booking h1 {
    color: var(--gold);
    margin-bottom: 40px;
}

/* FORM */
form {
    max-width: 500px;
    margin: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

input, textarea {
    padding: 12px;
    background: transparent;
    border: 1px solid #333;
    color: white;
}

input:focus, textarea:focus {
    outline: none;
    border-color: var(--gold);
}

button {
    padding: 12px;
    background: transparent;
    border: 1px solid var(--gold);
    color: var(--gold);
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: var(--gold);
    color: black;
}


     /* POPUP STYLING */
 .popup {
     display: none;
     position: fixed;
     top: 0;
     left: 0;
     width: 100%;
     height: 100%;
     background: rgba(0,0,0,0.7);
     justify-content: center;
     align-items: center;
 }

.popup-content {
    background: #111;
    padding: 25px;
    border: 1px solid #d8bd55;
    text-align: center;
    color: #fff;
    border-radius: 8px;
}

.popup-content h2 {
    color: #d8bd55;
}

.popup-content button {
    margin-top: 15px;
    padding: 8px 15px;
    background: #d8bd55;
    border: none;
    cursor: pointer;
}
</style>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Booking | Luxe Dining</title>
    <link rel="stylesheet" href="/CSS/booking.css" />
</head>

<body>

<header>
    <nav>
        <h1 class="logo">LUXE</h1>
        <ul>
            <li><a href="landingpage.html">Home</a></li>
            <li><a href="menu.html">Menu</a></li>
            <li><a href="booking.html">Booking</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
    </nav>
</header>

<section class="booking">
    <h1>Reserve a Table</h1>
    
   <form action="http://localhost/Luxe/submit-reservation.php" method="POST">
    <input type="text" name="first_name" placeholder="First Name" required />
    <input type="text" name="last_name" placeholder="Last Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="tel" name="phone" placeholder="Phone Number (Optional)" />
    <input type="date" name="reservation_date" required />
    <input type="time" name="reservation_time" required />
    <input type="number" name="number_of_guests" placeholder="Number of Guests" required />
    <input type="text" name="occasion" placeholder="Occasion (Optional)" />
    <input type="text" name="special_requests" placeholder="Special Requests (Optional)" />

    <button type="submit">Book Now</button>
</form>
</section>

<!-- POPUP -->
<div id="popup" class="popup">
    <div class="popup-content">
        <h2>BOOKING CONFIRMED</h2>
        <p>Your table has been successfully reserved.</p>
        <button onclick="closePopup()">OK</button>
    </div>
</div>

<script>
    const form = document.getElementById("bookingForm");
    const popup = document.getElementById("popup");

    form.addEventListener("submit", function(e){
        e.preventDefault();
        popup.style.display = "flex";
    });

    function closePopup(){
        popup.style.display = "none";
        form.reset();
    }
</script>

</body>
</html>