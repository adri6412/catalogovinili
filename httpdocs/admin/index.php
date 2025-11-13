<?php
session_start();
include '../config.php';
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login.php");
    exit();
}
 
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
 
$selectedVinylData = array(); // Array per memorizzare i dati del vinile selezionato

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se viene selezionato un vinile dal menu a discesa
    if (isset($_POST['selectedVinyl'])) {
        $selectedVinyl = mysqli_real_escape_string($conn, $_POST['selectedVinyl']);

        // Query per ottenere i dati del vinile selezionato
        $sqlSelectVinylData = "SELECT * FROM vinili WHERE Titolo like \"%$selectedVinyl%\"";
        $resultSelectVinylData = $conn->query($sqlSelectVinylData);

        if ($resultSelectVinylData->num_rows > 0) {
            $selectedVinylData = $resultSelectVinylData->fetch_assoc();
        }
    }

    // Codice per l'aggiornamento e l'eliminazione dei vinili
    if (isset($_POST['artista']) && isset($_POST['newTitolo']) && isset($_POST['oldTitolo'])) {
        $artista = mysqli_real_escape_string($conn, $_POST['artista']);
        $newTitolo = mysqli_real_escape_string($conn, $_POST['newTitolo']); // Sanitizzazione del nuovo titolo
        $anno = mysqli_real_escape_string($conn, $_POST['anno']);
        $genere = mysqli_real_escape_string($conn, $_POST['genere']);
        $supporto = mysqli_real_escape_string($conn, $_POST['supporto']);
        $oldTitolo = mysqli_real_escape_string($conn, $_POST['oldTitolo']); // Vecchio titolo del vinile

        // Query per l'aggiornamento dei dati del vinile
        $sqlUpdateVinyl = "UPDATE vinili SET Artista = '$artista', Titolo = '$newTitolo', Anno = '$anno', Genere = '$genere', Supporto = '$supporto' WHERE Titolo = '$oldTitolo'";

        if ($conn->query($sqlUpdateVinyl) === TRUE) {
            echo "Vinile aggiornato con successo.";
        } else {
            echo "Errore durante l'aggiornamento del vinile: " . $conn->error;
        }
    }

    if (isset($_POST['deleteVinyl'])) {
        $titoloVinile = $_POST['deleteVinyl'];

        // Query per eliminare un vinile
        $sqlDeleteVinyl = "DELETE FROM vinili WHERE Titolo = '$titoloVinile'";

        if ($conn->query($sqlDeleteVinyl) === TRUE) {
            echo "Vinile eliminato con successo.";
        } else {
            echo "Errore durante l'eliminazione del vinile: " . $conn->error;
        }
    }

    if (isset($_POST['addArtista']) && isset($_POST['addTitolo'])) {
        $addArtista = $_POST['addArtista'];
        $addTitolo = $conn->real_escape_string($_POST['addTitolo']); // Sanitizzazione del titolo per evitare SQL Injection
        $addAnno = $_POST['addAnno'];
        $addGenere = $_POST['addGenere'];
        $addSupporto = $_POST['addSupporto'];

        // Query per aggiungere un nuovo vinile utilizzando una query preparata
        $sqlAddVinyl = $conn->prepare("INSERT INTO vinili (Artista, Titolo, Anno, Genere, Supporto) VALUES (?, ?, ?, ?, ?)");
        $sqlAddVinyl->bind_param("sssss", $addArtista, $addTitolo, $addAnno, $addGenere, $addSupporto);

        if ($sqlAddVinyl->execute()) {
            echo "Vinile aggiunto con successo.";
        } else {
            echo "Errore durante l'aggiunta del vinile: " . $conn->error;
        }

        $sqlAddVinyl->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestione Catalogo Vinili</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('598655.png');
            background-size: cover;
            background-repeat: no-repeat;
            color: #ffffff;
        }

        a {
            color: #ffa500;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Gestione Catalogo Vinili</h1>
        <a href="/" class="btn btn-primary">Home Page</a>
        <a href="?logout" class="btn btn-danger">Logout</a>
		<a href="/admin/index_ai.php" class="btn btn-primary">AI Album Recognition</a>
        <br><br>

        <h2 class="mt-4">Modifica/Cancella Vinile</h2>
        <form method="POST">
            <div class="form-group">
                <label for="selectedVinyl">Seleziona il vinile da modificare:</label>
                <select name="selectedVinyl" class="form-control" onchange="this.form.submit()">
                    <option value="">Seleziona un vinile</option>
                    <?php
                    $sqlSelectVinyl = "SELECT Titolo FROM vinili";
                    $resultSelectVinyl = $conn->query($sqlSelectVinyl);

                    if ($resultSelectVinyl->num_rows > 0) {
                        while ($rowSelectVinyl = $resultSelectVinyl->fetch_assoc()) {
                            $titolo = $rowSelectVinyl['Titolo'];
                            echo "<option value='" . $titolo . "'>" . $titolo . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </form>

        <?php if (!empty($selectedVinylData)) { ?>
            <form method="POST">
<div class="form-group">
    <label for="artista">Artista:</label>
    <input type="text" name="artista" class="form-control" value="<?= stripslashes($selectedVinylData['Artista']) ?>">
</div>
<div class="form-group">
    <label for="titolo">Titolo Attuale:</label>
    <input type="text" name="oldTitolo" class="form-control" value="<?= stripslashes($selectedVinylData['Titolo']) ?>" readonly>
</div>
<div class="form-group">
    <label for="newTitolo">Nuovo Titolo:</label>
    <input type="text" name="newTitolo" class="form-control" placeholder="Inserisci il nuovo titolo">
</div>
<div class="form-group">
    <label for="anno">Anno:</label>
    <input type="text" name="anno" class="form-control" value="<?= stripslashes($selectedVinylData['Anno']) ?>">
</div>
<div class="form-group">
    <label for="genere">Genere:</label>
    <input type="text" name="genere" class="form-control" value="<?= stripslashes($selectedVinylData['Genere']) ?>">
</div>
<div class="form-group">
    <label for="supporto">Supporto fisico:</label>
    <input type="text" name="supporto" class="form-control" value="<?= stripslashes($selectedVinylData['Supporto']) ?>">
</div>
                <button type="submit" class="btn btn-primary">Aggiorna Vinile</button>
            </form>
            <form method="POST">
                <input type="hidden" name="deleteVinyl" value="<?= $selectedVinylData['Titolo'] ?>">
                <button type="submit" class="btn btn-danger mt-2">Elimina Vinile</button>
            </form>
        <?php } ?>

        <h2 class="mt-4">Aggiungi Nuovo Vinile</h2>
        <form method="POST">
            <div class="form-group">
                <label for="addArtista">Artista:</label>
                <input type="text" name="addArtista" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="addTitolo">Titolo:</label>
                <input type="text" name="addTitolo" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="addAnno">Anno:</label>
                <input type="text" name="addAnno" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="addGenere">Genere:</label>
                <input type="text" name="addGenere" class="form-control">
            </div>
            <div class="form-group">
                <label for="addSupporto">Supporto fisico:</label>
                <input type="text" name="addSupporto" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Aggiungi Vinile</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>