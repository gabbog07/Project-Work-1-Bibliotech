<?php
include __DIR__ . '/config_db.php';

$messaggio = "";

if (isset($_POST['registra'])) {
    $nome     = $_POST['nome'];
    $cognome  = $_POST['cognome'];
    $email    = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $token    = uniqid();

    // PREPARED STATEMENT: controlla duplicati
    $stmt_check = mysqli_prepare($conn, "SELECT id_utente FROM utenti WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        $messaggio = "<div class='alert alert-danger'>Username o email già in uso. Scegline altri.</div>";
    } else {
        
        // PREPARED STATEMENT: inserimento sicuro
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO utenti (nome, cognome, email, username, password_hash, activation_token, ruolo, stato_acc) VALUES (?, ?, ?, ?, ?, ?, 'studente', 'non_confermato')");
        mysqli_stmt_bind_param($stmt_insert, "ssssss", $nome, $cognome, $email, $username, $password, $token);

        if (mysqli_stmt_execute($stmt_insert)) {
            $link  = "http://localhost:8080/conferma.php?token=$token";
            $corpo = "<h2>Benvenuto " . htmlspecialchars($nome) . "!</h2>
                      <p>Clicca qui per attivare il tuo account: <a href='$link'>$link</a></p>";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: BiblioTech <noreply@bibliotech.it>\r\n";

            if (mail($email, "Attiva account BiblioTech", $corpo, $headers)) {
                $messaggio = "<div class='alert alert-success'>Registrazione completata! Controlla <a href='http://localhost:8025' target='_blank'>Mailpit (porta 8025)</a> per attivare l'account.</div>";
            } else {
                $messaggio = "<div class='alert alert-warning'>Utente registrato, ma errore invio email. Prova a controllare comunque Mailpit.</div>";
            }
        } else {
            $messaggio = "<div class='alert alert-danger'>Errore registrazione: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Registrazione</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $messaggio; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cognome</label>
                                <input type="text" name="cognome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="registra" class="btn btn-primary w-100">Registrati</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>Hai già un account? <a href="login.php">Accedi</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>