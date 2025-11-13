<?php
session_start();
include 'config.php'; // Includi il file di configurazione del database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ottieni i dati dal modulo di login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Esegui una query per cercare l'utente nel database
    $sql = "SELECT * FROM utenti WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verifica la password
        if (password_verify($password, $row['password'])) {
            // Login riuscito
            $_SESSION['authenticated'] = true;
            header("Location: index.php"); // Reindirizza l'utente alla home page
            exit(); // Assicurati di uscire per evitare l'esecuzione successiva del codice
        } else {
            echo '<div class="alert alert-danger">Password errata. Riprova.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Utente non trovato. Riprova.</div>';
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="application-name" content="Catalogo Vinili" />
    <meta name="apple-mobile-web-app-title" content="Catalogo Vinili" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <link rel="apple-touch-icon" href="/logo.png" />
    <link rel="manifest" href="/manifest.json">
    <!-- Includi il link ai file CSS di Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Aggiungi uno stile per il background del body */
body {
    background-image: url('598655.png'); /* Specifica l'URL del tuo file di immagine */
    background-size: cover; /* Scala l'immagine per coprire l'intero body */
    background-repeat: no-repeat; /* Impedisce la ripetizione dell'immagine */
    background-attachment: fixed; /* Impedisce lo scorrimento dell'immagine */
    color: #ffffff; /* Cambia il colore del testo in bianco */
    padding: 20px; /* Aggiungi spazio intorno al contenuto */
}

    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Login</h1>

        <!-- Form per il login -->
        <form method="POST">
            <div class="form-group">
                <label for="username">Nome utente:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Accedi</button>
        </form>
    </div>

    <!-- Includi i file JavaScript di Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>