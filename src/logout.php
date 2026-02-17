<?php
include __DIR__ . '/config_db.php';

// --- Invalida sessione nel DB (sezione 5.3 documentazione) ---
if (isset($_SESSION['user_id'])) {
    $id_sessione = mysqli_real_escape_string($conn, session_id());
    mysqli_query($conn, "DELETE FROM sessioni WHERE id_sessione = '$id_sessione'");
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>