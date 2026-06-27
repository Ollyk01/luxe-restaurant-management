<?php
require_once 'includes/config.php';
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

.contact {
    padding: 120px 50px;
    text-align: center;
}

.contact h1 {
    color: var(--gold);
    margin-bottom: 20px;
}

.contact p {
    margin-bottom: 10px;
    color: var(--gray);
}

/* FORM */
form {
    max-width: 500px;
    margin: 40px auto;
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

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact | Luxe Dining</title>
    <link rel="stylesheet" href="/CSS/contact.css" />
</head>
<body>

<header>
    <nav>
        <h1 class="logo">LUXE</h1>
        <ul>
            <li><a href="landingpage.php">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="booking.php">Booking</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<section class="contact">
    <h1>Contact Us</h1>

    <p>Email: info@luxedining.com</p>
    <p>Phone: +27 73 000 0000</p>
    <p>Location: Cape Town, South Africa</p>

    <form>
        <input type="text" placeholder="Your Name" required />
        <input type="email" placeholder="Your Email" required />
        <textarea placeholder="Your Message"></textarea>
        <button type="submit">Send Message</button>
    </form>
</section>

</body>
</html>