<?php
require_once __DIR__ . '/config_db.php';

/**
 * Effettua un prestito usando query classiche
 */
function effettuaPrestito($conn, $id_utente, $id_libro) {
    // Rendiamo i dati sicuri (trasformandoli in numeri interi)
    $id_utente = (int)$id_utente;
    $id_libro = (int)$id_libro;

    // Inizia la transazione (fondamentale per non perdere libri!)
    mysqli_query($conn, "START TRANSACTION");
    
    // 1. Controlliamo se il libro è disponibile
    $sql_check = "SELECT copie_disponibili FROM libri WHERE id_libro = $id_libro FOR UPDATE";
    $res_check = mysqli_query($conn, $sql_check);
    $libro = mysqli_fetch_assoc($res_check);

    if (!$libro || $libro['copie_disponibili'] <= 0) {
        mysqli_query($conn, "ROLLBACK"); // Annulla tutto
        return false;
    }

    // 2. Togliamo una copia dal magazzino
    $sql_update = "UPDATE libri SET copie_disponibili = copie_disponibili - 1 WHERE id_libro = $id_libro";
    $res_update = mysqli_query($conn, $sql_update);

    // 3. Registriamo il prestito
    $sql_insert = "INSERT INTO prestiti (id_utente, id_libro, data_inizio) VALUES ($id_utente, $id_libro, NOW())";
    $res_insert = mysqli_query($conn, $sql_insert);

    // Se tutto è andato bene, salviamo davvero i cambiamenti
    if ($res_update && $res_insert) {
        mysqli_query($conn, "COMMIT");
        return true;
    } else {
        mysqli_query($conn, "ROLLBACK");
        return false;
    }
}

/**
 * Registra una restituzione
 */
function registraRestituzione($conn, $id_prestito) {
    $id_prestito = (int)$id_prestito;

    mysqli_query($conn, "START TRANSACTION");

    // 1. Cerchiamo quale libro era in prestito
    $sql_p = "SELECT id_libro FROM prestiti WHERE id_prestito = $id_prestito AND data_fine IS NULL";
    $res_p = mysqli_query($conn, $sql_p);
    $prestito = mysqli_fetch_assoc($res_p);

    if (!$prestito) {
        mysqli_query($conn, "ROLLBACK");
        return false;
    }

    $id_libro = $prestito['id_libro'];

    // 2. Chiudiamo il prestito mettendo la data di oggi
    $sql_close = "UPDATE prestiti SET data_fine = NOW() WHERE id_prestito = $id_prestito";
    
    // 3. Aggiungiamo di nuovo la copia al libro
    $sql_inc = "UPDATE libri SET copie_disponibili = copie_disponibili + 1 WHERE id_libro = $id_libro";

    if (mysqli_query($conn, $sql_close) && mysqli_query($conn, $sql_inc)) {
        mysqli_query($conn, "COMMIT");
        return true;
    } else {
        mysqli_query($conn, "ROLLBACK");
        return false;
    }
}
?>