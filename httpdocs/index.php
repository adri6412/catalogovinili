<?php

session_start();
include 'config.php'; // Includi il file di configurazione del database



if (!isset($_SESSION['authenticated'])) {

    header("Location: login.php"); // Reindirizza l'utente alla pagina di login se non è autenticato

    exit();

}



if (isset($_GET['logout'])) {

    session_destroy(); // Termina la sessione

    header("Location: login.php"); // Reindirizza l'utente alla pagina di login dopo il logout

    exit();

}


// Inizializza le variabili dei filtri

$filterArtist = $filterGenre = $filterYear = $searchInput = '';



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $filterArtist = $_GET['filterArtist'];

    $filterGenre = $_GET['filterGenre'];

    $filterYear = $_GET['filterYear'];

    $searchInput = $_GET['searchInput'];

}



// Costruisci la query SQL dinamicamente basata sui filtri

$sql = "SELECT * FROM vinili WHERE 1=1";



if (!empty($searchInput)) {

    $sql .= " AND (Artista LIKE '%$searchInput%' OR Titolo LIKE '%$searchInput%')";

}



if (!empty($filterArtist)) {

    $sql .= " AND Artista = '$filterArtist'";

}



if (!empty($filterGenre)) {

    $sql .= " AND Genere = '$filterGenre'";

}



if (!empty($filterYear)) {

    $sql .= " AND Anno = '$filterYear'";

}



// Esegui la query

$result = $conn->query($sql);

?>



<!DOCTYPE html>

<html>

<head>

    <title>Catalogo Vinili</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="application-name" content="Catalogo Vinili" />
    <meta name="apple-mobile-web-app-title" content="Catalogo Vinili" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <link rel="apple-touch-icon" href="/logo.png" />
    <link rel="manifest" href="/manifest.json">
	<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- Aggiungi link ai file CSS di Bootstrap -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

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
		/* Aggiungi uno stile per il popup */
        #popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #ddd;
            padding: 20px;
            background: #fff;
            z-index: 1000;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        /* Stile per il link di chiusura del popup */
        #close-popup {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

    </style>
</head>

<body>

    <div class="container">

        <h1 class="mt-4">Catalogo Vinili</h1>

        <a href="?logout" class="btn btn-danger mt-2">Logout</a>

        <a href="/admin/" class="btn btn-primary mt-2">Area Amministrativa</a>

        <form class="form-group mt-4" method="GET">

    <div class="row">

        <div class="col">

            <input type="text" name="searchInput" class="form-control" placeholder="Cerca vinili..." value="<?php echo $searchInput; ?>">

        </div>

        <div class="col">

            <select name="filterArtist" class="form-control">

                <option value="">Filtra per Artista</option>

                <?php

                // Elenca gli artisti dal database

                $sqlArtist = "SELECT DISTINCT Artista FROM vinili";

                $resultArtist = $conn->query($sqlArtist);



                while ($rowArtist = $resultArtist->fetch_assoc()) {

                    $selected = ($rowArtist['Artista'] == $filterArtist) ? 'selected' : '';

                    echo "<option value='" . $rowArtist['Artista'] . "' $selected>" . $rowArtist['Artista'] . "</option>";

                }

                ?>

            </select>

        </div>

        <div class="col">

            <select name="filterGenre" class="form-control">

                <option value="">Filtra per Genere</option>

                <?php

                // Elenca i generi dal database

                $sqlGenre = "SELECT DISTINCT Genere FROM vinili";

                $resultGenre = $conn->query($sqlGenre);



                while ($rowGenre = $resultGenre->fetch_assoc()) {

                    $selected = ($rowGenre['Genere'] == $filterGenre) ? 'selected' : '';

                    echo "<option value='" . $rowGenre['Genere'] . "' $selected>" . $rowGenre['Genere'] . "</option>";

                }

                ?>

            </select>

        </div>

        <div class="col">

            <select name="filterYear" class="form-control">

                <option value="">Filtra per Anno</option>

                <?php

                // Elenca gli anni dal database

                $sqlYear = "SELECT DISTINCT Anno FROM vinili";

                $resultYear = $conn->query($sqlYear);



                while ($rowYear = $resultYear->fetch_assoc()) {

                    $selected = ($rowYear['Anno'] == $filterYear) ? 'selected' : '';

                    echo "<option value='" . $rowYear['Anno'] . "' $selected>" . $rowYear['Anno'] . "</option>";

                }

                ?>

            </select>

        </div>

        <div class="col">

            <button type="submit" class="btn btn-primary">Cerca</button>

        </div>

    </div>

</form>



        <?php

        if ($result->num_rows > 0) {

            echo "<div class='alert alert-info'>Hai un totale di " . $result->num_rows . " dischi.</div>";

        ?>

        <div class="table-responsive mt-4">

            <table class="table table-striped">

                <thead>

                    <tr>

                        <th class="text-light">Artista</th>

                        <th class="text-light">Titolo</th>

                        <th class="text-light">Anno</th>

                        <th class="text-light">Genere</th>
						<th class="text-light">Prezzo</th> <!-- Aggiunta colonna Prezzo -->
                    </tr>

                </thead>

                <tbody>

                    <?php

                    while ($row = $result->fetch_assoc()):

                    ?>

                    <tr>

                        <td class="text-light"><?php echo $row['Artista']; ?></td>

                        <td class="text-light"><?php echo $row['Titolo']; ?></td>

                        <td class="text-light"><?php echo $row['Anno']; ?></td>

                        <td class="text-light"><?php echo $row['Genere']; ?></td>
                        <td>
                             <!-- Aggiunto onclick per aprire il popup -->
                            <button class="btn btn-primary" onclick="openPopup('<?php echo urlencode($row['Artista'] . ' ' . $row['Titolo']);                             ?>')">
                           Cerca su Ebay
                           </button>


                        </td>
                    </tr>

                    <?php

                    endwhile;

                    ?>

                </tbody>

            </table>

        </div>

        <?php

        } else {

            echo "<div class='alert alert-warning'>Nessun risultato trovato.</div>";

        }

        $conn->close();

        ?>

    </div>



    <!-- Aggiungi script di Bootstrap e jQuery -->
     <script>
      function openPopup(keyword) {
        // Codice per aprire una finestra popup con la URL desiderata
        var popupUrl = "search.php?keyword=" + keyword;
        window.open(popupUrl, "_blank", "width=600,height=400,scrollbars=yes");
    }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>



