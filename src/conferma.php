<?php
include __DIR__ . '/config_db.php';

$token = mysqli_real_escape_string($conn, $_GET['token']);

$sql = "UPDATE utenti SET stato_acc = 'attivo', activation_token = NULL WHERE activation_token = '$token'";

if (mysqli_query($conn, $sql)) {
    $messaggio = "<div class='alert alert-success'>Account attivato! Puoi fare login.</div>";
} else {
    $messaggio = "<div class='alert alert-danger'>Errore attivazione.</div>";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Conferma Account</h4>
                    </div>
                    <div class="card-body text-center">
                        <?php echo $messaggio; ?>
                        <a href="login.php" class="btn btn-primary mt-3">Vai al Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>