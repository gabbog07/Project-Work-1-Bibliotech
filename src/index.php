<?php
include __DIR__ . '/config_db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="hero-box">
                    <h1 class="display-3 mb-3">📚 BiblioTech</h1>
                    <p class="lead">Sistema Gestione Biblioteca Scolastica</p>
                    <hr class="my-4">
                    
                    <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                        <a href="login.php" class="btn btn-primary btn-lg">Accedi</a>
                        <a href="registrazione.php" class="btn btn-outline-secondary btn-lg">Registrati</a>
                    </div>
                    
                    <div class="mt-5">
                        <small class="text-muted">Progetto Scolastico 2026</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>