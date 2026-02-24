<?php
include __DIR__ . '/config_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'studente') {
    header("Location: dashboard.php");
    exit();
}

$id_studente = $_SESSION['user_id'];

// PREPARED STATEMENT
$stmt = mysqli_prepare($conn, "SELECT p.*, l.titolo, l.autore FROM prestiti p JOIN libri l ON p.id_libro = l.id_libro WHERE p.id_utente = ? AND p.data_fine IS NULL ORDER BY p.data_inizio DESC");
mysqli_stmt_bind_param($stmt, "i", $id_studente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Prestiti - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-info">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">📚 BiblioTech</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Esci</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>I Miei Prestiti Attivi</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row mt-3">
                <?php while($prestito = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($prestito['titolo']); ?></h5>
                                <p class="card-text text-muted">
                                    <strong>Autore:</strong> <?php echo htmlspecialchars($prestito['autore']); ?>
                                </p>
                                <hr>
                                <small>
                                    <strong>Data ritiro:</strong>
                                    <?php echo date('d/m/Y', strtotime($prestito['data_inizio'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-3">
                Non hai prestiti attivi. <a href="libri.php">Vai al catalogo</a>
            </div>
        <?php endif; ?>

        <a href="libri.php" class="btn btn-primary mt-3">Torna al Catalogo</a>
    </div>
</body>
</html>