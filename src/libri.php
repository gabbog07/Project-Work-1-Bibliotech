<?php
include __DIR__ . '/config_db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$messaggio = "";

// Prestito libro
if (isset($_GET['prendi'])) {
    $id_libro = mysqli_real_escape_string($conn, $_GET['prendi']);
    $id_utente = $_SESSION['user_id'];
    
    // Controlla se ci sono copie
    $check = mysqli_query($conn, "SELECT copie_disponibili FROM libri WHERE id_libro = $id_libro");
    $libro = mysqli_fetch_assoc($check);
    
    if ($libro['copie_disponibili'] > 0) {
        // Scala copia
        mysqli_query($conn, "UPDATE libri SET copie_disponibili = copie_disponibili - 1 WHERE id_libro = $id_libro");
        
        // Crea prestito
        mysqli_query($conn, "INSERT INTO prestiti (id_utente, id_libro, data_inizio) VALUES ($id_utente, $id_libro, NOW())");
        
        $messaggio = "<div class='alert alert-success'>Prestito registrato!</div>";
    } else {
        $messaggio = "<div class='alert alert-danger'>Nessuna copia disponibile!</div>";
    }
}

$result = mysqli_query($conn, "SELECT * FROM libri ORDER BY titolo");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogo - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">📚 BiblioTech</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Esci</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Catalogo Libri</h2>
        
        <?php echo $messaggio; ?>
        
        <table class="table table-striped mt-3">
            <thead class="table-primary">
                <tr>
                    <th>Titolo</th>
                    <th>Autore</th>
                    <th>ISBN</th>
                    <th>Copie Totali</th>
                    <th>Disponibili</th>
                    <?php if ($_SESSION['user_role'] == 'studente'): ?>
                        <th>Azione</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while($libro = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $libro['titolo']; ?></td>
                    <td><?php echo $libro['autore']; ?></td>
                    <td><?php echo $libro['isbn']; ?></td>
                    <td><?php echo $libro['copie_totali']; ?></td>
                    <td>
                        <span class="badge <?php echo $libro['copie_disponibili'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $libro['copie_disponibili']; ?>
                        </span>
                    </td>
                    <?php if ($_SESSION['user_role'] == 'studente'): ?>
                        <td>
                            <?php if ($libro['copie_disponibili'] > 0): ?>
                                <a href="?prendi=<?php echo $libro['id_libro']; ?>" class="btn btn-sm btn-primary">Prendi</a>
                            <?php else: ?>
                                <span class="text-muted">Non disponibile</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if ($_SESSION['user_role'] == 'studente'): ?>
            <a href="miei_prestiti.php" class="btn btn-info">I Miei Prestiti</a>
        <?php endif; ?>
    </div>
</body>
</html>