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

// Se l'utente è loggato, verifica che IP e browser siano gli stessi del login
if (isset($_SESSION['user_id'])) {

    $ip_corrente    = $_SERVER['REMOTE_ADDR'];
    $agent_corrente = $_SERVER['HTTP_USER_AGENT'];

    if ($_SESSION['ip_address'] !== $ip_corrente || $_SESSION['user_agent'] !== $agent_corrente) {
        // IP o browser cambiati: possibile session hijacking, forza logout
        $id_sessione = mysqli_real_escape_string($conn, session_id());
        mysqli_query($conn, "DELETE FROM sessioni WHERE id_sessione = '$id_sessione'");

        session_unset();
        session_destroy();
        header("Location: login.php?errore=sessione");
        exit();
    }
}
?>