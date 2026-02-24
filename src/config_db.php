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

// Controllo IP e User-Agent ad ogni pagina
if (isset($_SESSION['user_id'])) {

    $ip_corrente    = $_SERVER['REMOTE_ADDR'];
    $agent_corrente = $_SERVER['HTTP_USER_AGENT'];

    if ($_SESSION['ip_address'] !== $ip_corrente || $_SESSION['user_agent'] !== $agent_corrente) {
        // Session hijacking: forza logout
        $id_sessione = session_id();
        
        // PREPARED STATEMENT
        $stmt = mysqli_prepare($conn, "DELETE FROM sessioni WHERE id_sessione = ?");
        mysqli_stmt_bind_param($stmt, "s", $id_sessione);
        mysqli_stmt_execute($stmt);

        session_unset();
        session_destroy();
        header("Location: login.php?errore=sessione");
        exit();
    }
}
?>