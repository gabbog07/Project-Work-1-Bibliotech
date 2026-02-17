<?php
session_start();

$servername = "bibliotech-db";
$username = "root";
$password = "root";
$dbname = "bibliotech";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Errore connessione: " . mysqli_connect_error());
}
?>