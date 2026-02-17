<?php
include __DIR__ . '/config_db.php';

$errore = "";

if (isset($_POST['accedi'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM utenti WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    $utente = mysqli_fetch_assoc($result);
    
    if ($utente && password_verify($password, $utente['password_hash'])) {
        
        if ($utente['stato_acc'] != 'attivo') {
            $errore = "Devi confermare l'account via email!";
        } else {
            // Genera codice 2FA
            $codice = rand(100000, 999999);
            $id = $utente['id_utente'];
            
            mysqli_query($conn, "UPDATE utenti SET cod_2FA = '$codice' WHERE id_utente = $id");
            
            // Salva in sessione
            $_SESSION['temp_id'] = $id;
            $_SESSION['temp_ruolo'] = $utente['ruolo'];
            $_SESSION['temp_nome'] = $utente['nome'];
            
            // Invia email
            $corpo = "<h2>Codice di Accesso</h2><p>Il tuo codice è: <h1>$codice</h1></p>";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: BiblioTech <noreply@bibliotech.it>\r\n";
            
            mail($utente['email'], "Codice 2FA", $corpo, $headers);
            
            header("Location: verifica_2fa.php");
            exit();
        }
    } else {
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
                        <?php if($errore): ?>
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