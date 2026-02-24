<?php
include __DIR__ . '/config_db.php';

$errore = "";

if (isset($_POST['accedi'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // PREPARED STATEMENT: protegge da SQL injection
    $stmt = mysqli_prepare($conn, "SELECT * FROM utenti WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $utente = mysqli_fetch_assoc($result);

    if ($utente) {

        // Controlla se l'account è bloccato
        if ($utente['blocco_fino'] !== NULL && strtotime($utente['blocco_fino']) > time()) {
            $minuti_rimasti = ceil((strtotime($utente['blocco_fino']) - time()) / 60);
            $errore = "Account bloccato per troppi tentativi. Riprova tra $minuti_rimasti minuto/i.";

        } elseif (password_verify($password, $utente['password_hash'])) {

            // Password corretta
            if ($utente['stato_acc'] == 'bloccato') {
                $errore = "Account bloccato. Contatta il bibliotecario.";

            } elseif ($utente['stato_acc'] != 'attivo') {
                $errore = "Devi confermare l'account via email!";

            } else {
                $id = $utente['id_utente'];
                
                // Reset tentativi falliti
                $stmt_reset = mysqli_prepare($conn, "UPDATE utenti SET tentativi_login = 0, blocco_fino = NULL WHERE id_utente = ?");
                mysqli_stmt_bind_param($stmt_reset, "i", $id);
                mysqli_stmt_execute($stmt_reset);

                // Genera codice 2FA
                $codice = rand(100000, 999999);
                $stmt_2fa = mysqli_prepare($conn, "UPDATE utenti SET cod_2FA = ? WHERE id_utente = ?");
                mysqli_stmt_bind_param($stmt_2fa, "si", $codice, $id);
                mysqli_stmt_execute($stmt_2fa);

                // Salva dati temporanei in sessione
                $_SESSION['temp_id']    = $id;
                $_SESSION['temp_ruolo'] = $utente['ruolo'];
                $_SESSION['temp_nome']  = $utente['nome'];

                // Invia email con codice 2FA
                $corpo    = "<h2>Codice di Accesso BiblioTech</h2><p>Il tuo codice è: <strong style='font-size:2em;'>$codice</strong></p>";
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=utf-8\r\n";
                $headers .= "From: BiblioTech <noreply@bibliotech.it>\r\n";
                mail($utente['email'], "Codice 2FA BiblioTech", $corpo, $headers);

                header("Location: verifica_2fa.php");
                exit();
            }

        } else {
            // Password errata: incrementa tentativi
            $tentativi = $utente['tentativi_login'] + 1;
            sleep(2);

            if ($tentativi >= 5) {
                // Blocco per 15 minuti
                $blocco = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $id = $utente['id_utente'];
                
                $stmt_block = mysqli_prepare($conn, "UPDATE utenti SET tentativi_login = ?, blocco_fino = ? WHERE id_utente = ?");
                mysqli_stmt_bind_param($stmt_block, "isi", $tentativi, $blocco, $id);
                mysqli_stmt_execute($stmt_block);
                
                $errore = "Troppi tentativi falliti. Account bloccato per 15 minuti.";
            } else {
                $rimasti = 5 - $tentativi;
                $id = $utente['id_utente'];
                
                $stmt_inc = mysqli_prepare($conn, "UPDATE utenti SET tentativi_login = ? WHERE id_utente = ?");
                mysqli_stmt_bind_param($stmt_inc, "ii", $tentativi, $id);
                mysqli_stmt_execute($stmt_inc);
                
                $errore = "Credenziali errate! Tentativi rimasti: $rimasti.";
            }
        }

    } else {
        sleep(2);
        $errore = "Credenziali errate!";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Accedi</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($errore): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($errore); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="accedi" class="btn btn-primary w-100">Accedi</button>
                        </form>

                        <hr>
                        <div class="text-center">
                            <small><a href="reset_password.php">Password dimenticata?</a></small>
                        </div>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">
                                <strong>Account di test:</strong><br>
                                admin / password (bibliotecario)<br>
                                mario88 / password (studente)
                            </small>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <small>Non hai un account? <a href="registrazione.php">Registrati</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>