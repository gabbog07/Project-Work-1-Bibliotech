<?php
include __DIR__ . '/config_db.php';

if (!isset($_SESSION['temp_id'])) {
    header("Location: login.php");
    exit();
}

$errore = "";

if (isset($_POST['verifica'])) {
    $codice = mysqli_real_escape_string($conn, $_POST['codice']);
    $id     = $_SESSION['temp_id'];

    $sql    = "SELECT cod_2FA FROM utenti WHERE id_utente = $id";
    $result = mysqli_query($conn, $sql);
    $riga   = mysqli_fetch_assoc($result);

    if ($codice == $riga['cod_2FA']) {

        // Login completato: popola sessione definitiva
        $_SESSION['user_id']     = $_SESSION['temp_id'];
        $_SESSION['user_role']   = $_SESSION['temp_ruolo'];
        $_SESSION['user_name']   = $_SESSION['temp_nome'];
        $_SESSION['login_time']  = date('Y-m-d H:i:s'); // sezione 5.3 documentazione
        $_SESSION['ip_address']  = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent']  = $_SERVER['HTTP_USER_AGENT'];

        unset($_SESSION['temp_id']);
        unset($_SESSION['temp_ruolo']);
        unset($_SESSION['temp_nome']);

        // Resetta codice 2FA nel DB
        mysqli_query($conn, "UPDATE utenti SET cod_2FA = NULL WHERE id_utente = $id");

        // --- Salva sessione nel DB (sezione 5.3 + 7.1 documentazione) ---
        $id_sessione = session_id();
        $ip          = mysqli_real_escape_string($conn, $_SESSION['ip_address']);
        $agent       = mysqli_real_escape_string($conn, $_SESSION['user_agent']);
        $login_time  = $_SESSION['login_time'];

        mysqli_query($conn, "INSERT INTO sessioni (id_sessione, id_utente, ip_address, user_agent, login_time)
                             VALUES ('$id_sessione', $id, '$ip', '$agent', '$login_time')
                             ON DUPLICATE KEY UPDATE ip_address='$ip', user_agent='$agent', login_time='$login_time'");

        header("Location: dashboard.php");
        exit();

    } else {
        $errore = "Codice errato!";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica 2FA - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4>Verifica Codice</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-center">Inserisci il codice ricevuto via email</p>

                        <?php if ($errore): ?>
                            <div class="alert alert-danger"><?php echo $errore; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Codice OTP</label>
                                <input type="text" name="codice" class="form-control text-center" placeholder="123456" required>
                                <small class="text-muted">Controlla Mailpit: http://localhost:8025</small>
                            </div>
                            <button type="submit" name="verifica" class="btn btn-warning w-100">Verifica</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="login.php">Torna al login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>