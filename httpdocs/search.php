<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBay Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('598655.png');
            background-size: cover;
            background-repeat: no-repeat;
            color: #ffffff;
            padding: 20px; /* Aggiungi un po' di spazio al body per una migliore presentazione */
        }
    </style>
</head>
<body>

<?php

// Funzione di ricerca su eBay
function searchEbay($keyword) {
    // Usa variabile d'ambiente se disponibile, altrimenti usa il nome del servizio Docker
    $apiUrl = getenv('API_URL') ?: "http://api:3000";
    $url = $apiUrl . "/search?keyword=" . urlencode($keyword);
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Ottenere la keyword dalla query string
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";

// Esempio di ricerca per la keyword ottenuta dalla query string
$results = searchEbay($keyword);

?>

<div class="container">
    <?php if ($results && isset($results['itemSummaries']) && is_array($results['itemSummaries'])): ?>
        <h2>Dischi in vendita su eBay con Relativo Prezzo: <?php echo $keyword; ?></h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="text-light">Titolo</th>
                    <th scope="col" class="text-light">Prezzo (USD)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results['itemSummaries'] as $item): ?>
                    <tr>
                        <td class="text-light"><?php echo $item['title']; ?></td>
                        <td class="text-light"><?php echo $item['price']['value']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nessun risultato trovato per: <?php echo $keyword; ?></p>
    <?php endif; ?>
</div>

</body>
</html>
