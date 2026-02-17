<?php
include __DIR__ . '/config_db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$nome = $_SESSION['user_name'];
$ruolo = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">📚 BiblioTech</span>
            <div>
                <span class="text-white me-3">Ciao, <?php echo $nome; ?> (<?php echo $ruolo; ?>)</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Esci</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Benvenuto nella Dashboard</h2>
        
        <?php if ($ruolo == 'bibliotecario'): ?>
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5>Pannello Bibliotecario</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="gestisci_restituzioni.php" class="list-group-item list-group-item-action">
                            📦 Gestione Restituzioni
                        </a>
                        <a href="libri.php" class="list-group-item list-group-item-action">
                            📚 Catalogo Libri
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5>Pannello Studente</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="libri.php" class="list-group-item list-group-item-action">
                            📚 Catalogo Libri
                        </a>
                        <a href="miei_prestiti.php" class="list-group-item list-group-item-action">
                            📖 I Miei Prestiti
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>