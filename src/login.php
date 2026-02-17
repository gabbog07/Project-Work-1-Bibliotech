<?php
include __DIR__ . '/config_db.php';

$errore = "";

if (isset($_POST['accedi'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM utenti WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    $utente = mysqli_fetch_assoc($result);

    if ($utente) {

        // --- BRUTE FORCE: controlla se l'account è bloccato ---
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
                // Reset tentativi falliti
                mysqli_query($conn, "UPDATE utenti SET tentativi_login = 0, blocco_fino = NULL WHERE id_utente = " . $utente['id_utente']);

                // Genera codice 2FA
                $codice = rand(100000, 999999);
                $id     = $utente['id_utente'];
                mysqli_query($conn, "UPDATE utenti SET cod_2FA = '$codice' WHERE id_utente = $id");

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

            // Rallentamento artificiale (sezione 7.1 documentazione)
            sleep(2);

            if ($tentativi >= 5) {
                // Blocco per 15 minuti
                $blocco = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                mysqli_query($conn, "UPDATE utenti SET tentativi_login = $tentativi, blocco_fino = '$blocco' WHERE id_utente = " . $utente['id_utente']);
                $errore = "Troppi tentativi falliti. Account bloccato per 15 minuti.";
            } else {
                $rimasti = 5 - $tentativi;
                mysqli_query($conn, "UPDATE utenti SET tentativi_login = $tentativi WHERE id_utente = " . $utente['id_utente']);
                $errore = "Credenziali errate! Tentativi rimasti: $rimasti.";
            }
        }

    } else {
        // Username non esiste: sleep per non rivelare info
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
                            <div class="alert alert-danger"><?php echo $errore; ?></div>
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