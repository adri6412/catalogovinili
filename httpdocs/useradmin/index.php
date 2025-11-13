<!DOCTYPE html>
<html>
<head>
    <title>Aggiungi Utente</title>
    <!-- Includi il link ai file CSS di Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
 <style>
    /* Aggiungi uno stile per il background del body */
    body {
        background-image: url('598655.png'); /* Specifica l'URL del tuo file di immagine */
        background-size: cover; /* Scala l'immagine per coprire l'intero body */
        background-repeat: no-repeat; /* Impedisce la ripetizione dell'immagine */
        color: #ffffff; /* Cambia il colore del testo in bianco */
    }

    /* Cambia il colore del link */
    a {
        color: #ffa500; /* Cambia il colore del link (in questo caso, arancione) */
    }

    /* Cambia il colore del testo delle etichette */
    label {
        color: #000000; /* Specifica il colore del testo delle etichette in bianco */
    }
</style>
    <div class="container">
        <h1 class="mt-4">Aggiungi Utente</h1>
        
        <div class="card">
            <div class="card-body">
                <?php
                // Verifica se il modulo è stato inviato
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Connessione al database
              $servername = "catalozvinili.mysql.db";
              $username = "catalozvinili";
              $password = "Fd883d0ffa";
              $dbname = "catalozvinili";

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connessione al database fallita: " . $conn->connect_error);
                    }

                    // Ottieni i dati dal modulo
                    $username = $_POST['username'];
                    $password = $_POST['password'];

                    // Esegui l'hash della password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Inserisci i dati nel database
                    $sql = "INSERT INTO utenti (username, password) VALUES ('$username', '$hashed_password')";

                    if ($conn->query($sql) === TRUE) {
                        echo '<div class="alert alert-success">Utente aggiunto con successo.</div>';
                    } else {
                        echo '<div class="alert alert-danger">Errore durante l\'aggiunta dell\'utente: ' . $conn->error . '</div>';
                    }

                    $conn->close();
                }
                ?>

                <!-- Form per aggiungere un nuovo utente -->
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Aggiungi Utente</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Includi i file JavaScript di Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
