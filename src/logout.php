<?php
include __DIR__ . '/config_db.php';

// Invalida sessione nel DB
if (isset($_SESSION['user_id'])) {
    $id_sessione = session_id();
    
    // PREPARED STATEMENT
    $stmt = mysqli_prepare($conn, "DELETE FROM sessioni WHERE id_sessione = ?");
    mysqli_stmt_bind_param($stmt, "s", $id_sessione);
    mysqli_stmt_execute($stmt);
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>