<?php
include __DIR__ . '/config_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'bibliotecario') {
    die("Accesso negato");
}

$messaggio = "";

// Restituzione
if (isset($_GET['restituisci'])) {
    $id_prestito = mysqli_real_escape_string($conn, $_GET['restituisci']);
    
    // Prendi id libro
    $get_libro = mysqli_query($conn, "SELECT id_libro FROM prestiti WHERE id_prestito = $id_prestito");
    $prestito = mysqli_fetch_assoc($get_libro);
    $id_libro = $prestito['id_libro'];
    
    // Chiudi prestito
    mysqli_query($conn, "UPDATE prestiti SET data_fine = NOW() WHERE id_prestito = $id_prestito");
    
    // Rimetti copia
    mysqli_query($conn, "UPDATE libri SET copie_disponibili = copie_disponibili + 1 WHERE id_libro = $id_libro");
    
    $messaggio = "<div class='alert alert-success'>Restituzione registrata!</div>";
}

$sql = "SELECT p.id_prestito, p.data_inizio, u.nome, u.cognome, l.titolo, l.autore
        FROM prestiti p
        JOIN utenti u ON p.id_utente = u.id_utente
        JOIN libri l ON p.id_libro = l.id_libro
        WHERE p.data_fine IS NULL
        ORDER BY p.data_inizio";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Restituzioni - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">📚 BiblioTech - Admin</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Esci</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Gestione Restituzioni</h2>
        
        <?php echo $messaggio; ?>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="table table-striped mt-3">
                <thead class="table-warning">
                    <tr>
                        <th>Studente</th>
                        <th>Libro</th>
                        <th>Autore</th>
                        <th>Data Prestito</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($prestito = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $prestito['nome'] . " " . $prestito['cognome']; ?></td>
                        <td><?php echo $prestito['titolo']; ?></td>
                        <td><?php echo $prestito['autore']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($prestito['data_inizio'])); ?></td>
                        <td>
                            <a href="?restituisci=<?php echo $prestito['id_prestito']; ?>" 
                               class="btn btn-sm btn-success"
                               onclick="return confirm('Confermi la restituzione?')">
                                Registra Restituzione
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info mt-3">
                Nessun prestito attivo.
            </div>
        <?php endif; ?>
        
        <a href="libri.php" class="btn btn-primary mt-3">Visualizza Catalogo</a>
    </div>
</body>
</html>