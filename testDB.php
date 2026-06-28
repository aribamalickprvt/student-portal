<?php
$servername = "localhost";
$username = "root"; // CHANGE THIS to your MySQL username
$password = "ariba900"; // CHANGE THIS to your MySQL password
$dbname = "sakila"; // CHANGE THIS to a database you want to connect to

// 1. Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// 2. Check connection
if ($conn->connect_error) {
    // If connection fails, display the error and stop the script
    die("Connection failed: " . $conn->connect_error);
}

// 3. If successful
echo "<h1>Success!</h1>";
echo "<p>PHP has successfully connected to MySQL Server and the database: " . $dbname . "</p>";

// 4. Close connection
$conn->close();
?>