<?php
include __DIR__ . '/config_db.php';

$messaggio = "";

// --- STEP 1: Utente inserisce la mail ---
if (isset($_POST['richiedi_reset'])) {
    $email = $_POST['email'];

    $email_sql = mysqli_real_escape_string($conn, $email);
    $sql    = "SELECT id_utente, nome FROM utenti WHERE email = '$email_sql' AND stato_acc = 'attivo'";
    $result = mysqli_query($conn, $sql);
    $utente = mysqli_fetch_assoc($result);

    if ($utente) {
        // Genera token univoco con scadenza 30 minuti
        $token    = bin2hex(random_bytes(32));
        $scadenza = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $id       = $utente['id_utente'];

        // Invalida eventuali token precedenti
        mysqli_query($conn, "UPDATE reset_password SET usato = TRUE WHERE id_utente = $id AND usato = FALSE");

        // Inserisce nuovo token
        mysqli_query($conn, "INSERT INTO reset_password (id_utente, token, scadenza) VALUES ($id, '$token', '$scadenza')");

        // Invia email con link
        $link    = "http://localhost:8080/reset_password.php?token=$token";
        $corpo   = "<h2>Reset Password BiblioTech</h2>
                    <p>Ciao " . htmlspecialchars($utente['nome']) . ",</p>
                    <p>Clicca qui per impostare una nuova password:</p>
                    <p><a href='$link'>$link</a></p>
                    <p><small>Il link scade tra 30 minuti.</small></p>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: BiblioTech <noreply@bibliotech.it>\r\n";

        mail($email, "Reset Password BiblioTech", $corpo, $headers);
    }

    // Messaggio generico: non rivela se l'email esiste o no
    $messaggio = "<div class='alert alert-success'>Se l'email è registrata, riceverai un link. Controlla <a href='http://localhost:8025' target='_blank'>Mailpit</a>.</div>";
}

// --- STEP 2: Utente arriva dal link email e imposta nuova password ---
if (isset($_POST['nuova_password'])) {
    $token    = mysqli_real_escape_string($conn, $_POST['token']);
    $password = $_POST['password'];
    $conferma = $_POST['conferma'];

    if ($password !== $conferma) {
        $messaggio = "<div class='alert alert-danger'>Le password non coincidono.</div>";

    } elseif (strlen($password) < 6) {
        $messaggio = "<div class='alert alert-danger'>La password deve essere di almeno 6 caratteri.</div>";

    } else {
        // Verifica token valido e non scaduto
        $sql    = "SELECT r.id, r.id_utente FROM reset_password r
                   WHERE r.token = '$token' AND r.usato = FALSE AND r.scadenza > NOW()";
        $result = mysqli_query($conn, $sql);
        $reset  = mysqli_fetch_assoc($result);

        if ($reset) {
            $nuovo_hash = password_hash($password, PASSWORD_BCRYPT);
            $id_utente  = $reset['id_utente'];

            // Aggiorna password
            mysqli_query($conn, "UPDATE utenti SET password_hash = '$nuovo_hash' WHERE id_utente = $id_utente");

            // Segna token come usato
            mysqli_query($conn, "UPDATE reset_password SET usato = TRUE WHERE id = " . $reset['id']);

            $messaggio = "<div class='alert alert-success'>Password aggiornata! <a href='login.php'>Vai al login</a></div>";
        } else {
            $messaggio = "<div class='alert alert-danger'>Link non valido o scaduto. Richiedi un nuovo reset.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Recupero Password</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $messaggio; ?>

                        <?php if (isset($_GET['token'])): ?>
                            <!-- STEP 2: Form nuova password -->
                            <p>Inserisci la tua nuova password.</p>
                            <form method="POST">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nuova Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Conferma Password</label>
                                    <input type="password" name="conferma" class="form-control" required>
                                </div>
                                <button type="submit" name="nuova_password" class="btn btn-primary w-100">Aggiorna Password</button>
                            </form>

                        <?php else: ?>
                            <!-- STEP 1: Form richiesta reset -->
                            <p>Inserisci la tua email per ricevere il link di recupero.</p>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <button type="submit" name="richiedi_reset" class="btn btn-primary w-100">Invia Link di Reset</button>
                            </form>

                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <small><a href="login.php">Torna al login</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>