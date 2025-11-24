<?php
session_start();
include '../config.php'; // Assicurati di includere correttamente il file di configurazione del database

header('Content-Type: text/html; charset=utf-8');

// Verifica l'autenticazione dell'utente
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login.php");
    exit();
}

// Gestione del logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$title = $artist = $year = $genre = '';

// Elaborazione dell'immagine solo se è stato inviato un file correttamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['albumImage']) && $_FILES['albumImage']['error'] == 0) {
    $image = $_FILES['albumImage']['tmp_name'];

    // Leggi il contenuto dell'immagine
    $imageData = file_get_contents($image);
    $encodedImage = base64_encode($imageData);

    // Imposta l'API Key di OpenAI
    $apiKey = $openaiApiKey;

    // Variabile per raccogliere output di debug ed errori
    $debugOutput = '';

    // Check if it's an AJAX request
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

    if ($apiKey === 'YOUR_OPENAI_API_KEY' || empty($apiKey)) {
        $errorMsg = 'Errore: API Key di OpenAI non configurata in config.php.';
        if ($isAjax) {
            echo json_encode(['success' => false, 'error' => $errorMsg]);
            exit;
        }
        $debugOutput .= '<div class="alert alert-danger">' . $errorMsg . '</div>';
    } else {
        // Invia la richiesta all'API di OpenAI
        $url = "https://api.openai.com/v1/chat/completions";
        $headers = [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ];
        
        $promptText = "Identify this album based on its cover art. Use your knowledge of music albums to determine the details even if the text is stylized, obscured, or hard to read. 
        
        Extract the following information in JSON format:
        - artist: The name of the artist or band.
        - title: The title of the album.
        - year: The original release year (YYYY).
        - genre: The primary genre of the album (e.g., Rock, Jazz, Pop, Electronic, Classical).
        
        If you are not 100% sure about a specific field, make your best guess based on the visual style and context.
        Return ONLY the JSON object.";

        $data = [
            "model" => "gpt-4o",
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        ["type" => "text", "text" => $promptText],
                        ["type" => "image_url", "image_url" => ["url" => "data:image/jpeg;base64,$encodedImage"]]
                    ]
                ]
            ],
            "max_tokens" => 300,
            "response_format" => ["type" => "json_object"]
        ];

        // Inizializza la richiesta cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Accetta qualsiasi encoding (gzip, ecc.)
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout di 30 secondi

        // Esegui la richiesta cURL
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            // Decodifica la risposta JSON
            $responseData = json_decode($response, true);
            $jsonError = json_last_error();
            $jsonErrorMsg = json_last_error_msg();
            
            // Debug info
            $rawResponse = htmlspecialchars($response);
            $debugOutput .= '<details><summary>Show Raw API Response</summary><pre id="raw-ai-response">' . $rawResponse . '</pre></details>';

            if ($jsonError !== JSON_ERROR_NONE) {
                 $errorMsg = 'Errore nel parsing della risposta JSON principale. Error: ' . $jsonErrorMsg;
                 if ($isAjax) {
                     echo json_encode(['success' => false, 'error' => $errorMsg, 'debug' => $rawResponse]);
                     exit;
                 }
                 $debugOutput .= '<div class="alert alert-danger">' . $errorMsg . '</div>';
            } elseif (isset($responseData['choices'][0]['message']['content'])) {
                $content = $responseData['choices'][0]['message']['content'];
                
                // Pulisci il contenuto da eventuali backtick markdown (es. ```json ... ```)
                $content = str_replace(["```json", "```"], "", $content);
                $content = trim($content);
                
                $jsonContent = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $title = $jsonContent['title'] ?? 'N/A';
                    $artist = $jsonContent['artist'] ?? 'N/A';
                    $year = $jsonContent['year'] ?? 'N/A';
                    $genre = $jsonContent['genre'] ?? 'N/A';

                    $extractedData = [
                        'title' => $title,
                        'artist' => $artist,
                        'year' => $year,
                        'genre' => $genre
                    ];

                    if ($isAjax) {
                        echo json_encode(['success' => true, 'data' => $extractedData, 'debug' => $rawResponse]);
                        exit;
                    }
                } else {
                    $errorMsg = 'Errore nel parsing della risposta JSON dell\'AI. JSON Error: ' . json_last_error_msg();
                    if ($isAjax) {
                        echo json_encode(['success' => false, 'error' => $errorMsg, 'debug' => $rawResponse, 'raw_content' => $content]);
                        exit;
                    }
                    $debugOutput .= '<div class="alert alert-warning">' . $errorMsg . ' Raw content: <pre>' . htmlspecialchars($content) . '</pre></div>';
                }
            } else {
                 $errorMsg = 'Risposta dell\'AI vuota o malformata.';
                 if ($isAjax) {
                     echo json_encode(['success' => false, 'error' => $errorMsg, 'debug' => $rawResponse]);
                     exit;
                 }
                 $debugOutput .= '<div class="alert alert-warning">' . $errorMsg . ' Response: <pre>' . htmlspecialchars(print_r($responseData, true)) . '</pre></div>';
            }
        } else {
            $errorMsg = 'Errore nella richiesta API: ' . $httpCode . ' - ' . $curlError;
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => $errorMsg, 'debug' => $response]);
                exit;
            }
            $debugOutput .= '<div class="alert alert-danger">' . $errorMsg . ' <br> Response: ' . htmlspecialchars($response) . '</div>';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    // Inserimento dei dati nel database dalla conferma del form
    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    $year = $_POST['year'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $support = 'vinyl';

    // Connessione al database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica la connessione
    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

    // Prepara la query di inserimento
    $stmt = $conn->prepare("INSERT INTO vinili (Artista, Titolo, Anno, Genere, Supporto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $artist, $title, $year, $genre, $support);

    // Esegui la query
    if ($stmt->execute()) {
        echo "Dati inseriti nel database con successo.";
    } else {
        echo "Errore durante l'inserimento nel database: " . $conn->error;
    }

    // Chiudi lo statement e la connessione al database
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caricamento AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      
        body {
            background-image: url('598655.png');
            background-size: cover;
            background-repeat: no-repeat;
            color: #ffffff;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #000000;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
			color: #ffffff;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        label {
            font-weight: bold;
        }
        input[type="file"],
        input[type="text"],
        button {
            padding: 10px;
            font-size: 16px;
        }
        button {
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #response {
            margin-top: 20px;
        }
    </style>
    <script>
        let isFormSubmitted = false;

        async function analyzeImage() {
            if (isFormSubmitted) {
                return;
            }

            const formData = new FormData();
            formData.append('albumImage', document.getElementById('albumImage').files[0]);

            try {
                const response = await fetch('index_ai.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.text();
                document.getElementById('response').innerHTML = data;

                // Trova e visualizza i dati estratti
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const title = doc.querySelector('#title').value;
                const artist = doc.querySelector('#artist').value;
                const year = doc.querySelector('#year').value;
                const genre = doc.querySelector('#genre').value;

                const extractedData = {
                    title: title,
                    artist: artist,
                    year: year,
                    genre: genre
                };

                const dataString = JSON.stringify(extractedData, null, 2);

                const confirmation = confirm('Vuoi confermare l\'inserimento di questi dati?\n' + dataString);
                if (confirmation) {
                    isFormSubmitted = true;
                    document.getElementById('title').value = extractedData.title;
                    document.getElementById('artist').value = extractedData.artist;
                    document.getElementById('year').value = extractedData.year;
                    document.getElementById('genre').value = extractedData.genre;
                    document.getElementById('support').value = 'vinyl';
                    document.getElementById('vinylForm').submit();
                }

            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nel caricamento dell\'immagine.');
            }
        }
    </script>
    <script>
        // Override the analyzeImage function to include better validation
         async function analyzeImage() {
            if (isFormSubmitted) {
                return;
            }

            const fileInput = document.getElementById('albumImage');
            if (fileInput.files.length === 0) {
                alert("Seleziona un'immagine prima.");
                return;
            }

            // Show loading state
            const btn = document.querySelector('button[onclick="analyzeImage()"]');
            const originalText = btn.innerText;
            btn.innerText = "Elaborazione in corso...";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('albumImage', fileInput.files[0]);
            formData.append('ajax', '1'); // Request JSON response

            try {
                const response = await fetch('index_ai.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                console.log("Server Response:", result);

                // Show debug info if available
                if (result.debug) {
                    const responseDiv = document.getElementById('response');
                    responseDiv.innerHTML = `<details><summary>Show Raw API Response</summary><pre>${result.debug}</pre></details>`;
                }

                if (!result.success) {
                    alert("ERRORE: " + (result.error || "Errore sconosciuto"));
                    btn.innerText = originalText;
                    btn.disabled = false;
                    return;
                }

                const extractedData = result.data;
                const title = extractedData.title;
                const artist = extractedData.artist;
                const year = extractedData.year;
                const genre = extractedData.genre;

                console.log("Extracted Data:", extractedData);

                if (!title && !artist) {
                     alert("L'AI non ha trovato dati nell'immagine. Riprova.");
                     btn.innerText = originalText;
                     btn.disabled = false;
                     return;
                }

                // Create a preview of the data
                const previewHtml = `
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">Dati Estratti</div>
                        <div class="card-body text-dark">
                            <p><strong>Artista:</strong> ${artist}</p>
                            <p><strong>Titolo:</strong> ${title}</p>
                            <p><strong>Anno:</strong> ${year}</p>
                            <p><strong>Genere:</strong> ${genre}</p>
                            <button id="confirmSaveBtn" class="btn btn-success btn-lg btn-block">Salva nel Database</button>
                        </div>
                    </div>
                `;

                // Append preview to response div
                const responseDiv = document.getElementById('response');
                const previewDiv = document.createElement('div');
                previewDiv.innerHTML = previewHtml;
                responseDiv.insertBefore(previewDiv, responseDiv.firstChild);

                // Add event listener to the new button
                document.getElementById('confirmSaveBtn').addEventListener('click', function() {
                    isFormSubmitted = true;
                    document.getElementById('title').value = extractedData.title;
                    document.getElementById('artist').value = extractedData.artist;
                    document.getElementById('year').value = extractedData.year;
                    document.getElementById('genre').value = extractedData.genre;
                    document.getElementById('support').value = 'vinyl';
                    document.getElementById('vinylForm').submit();
                });

                // Reset button state
                btn.innerText = originalText;
                btn.disabled = false;

            } catch (error) {
                console.error('Errore:', error);
                // Try to get text response if JSON failed
                alert('Errore di comunicazione con il server. Controlla la console.');
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }
    </script>
</head>
<body>
    <div class="container">
		<a href="/" class="btn btn-primary">Home Page</a>
        <a href="?logout" class="btn btn-danger">Logout</a>
        <br><br>
        <h1>Inserisci un nuovo vinile</h1>
        
        <?php if (!empty($debugOutput)) echo $debugOutput; ?>

        <form id="vinylForm" action="index_ai.php" method="post" enctype="multipart/form-data">
            <label for="albumImage">Carica l'immagine dell'album:</label>
            <input type="file" name="albumImage" id="albumImage" required>
            <input type="hidden" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>">
            <input type="hidden" name="artist" id="artist" value="<?php echo htmlspecialchars($artist); ?>">
            <input type="hidden" name="year" id="year" value="<?php echo htmlspecialchars($year); ?>">
            <input type="hidden" name="genre" id="genre" value="<?php echo htmlspecialchars($genre); ?>">  <!-- Aggiunto il genere -->
            <input type="hidden" name="support" id="support" value="vinyl"> <!-- Aggiunto il supporto -->
            <button type="button" onclick="analyzeImage()">Carica</button>
        </form>
        <div id="response"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
    
    <?php if (isset($extractedData)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('Dati Inseriti Correttamente');
            document.getElementById('title').value = '<?php echo addslashes($extractedData['title']); ?>';
            document.getElementById('artist').value = '<?php echo addslashes($extractedData['artist']); ?>';
            document.getElementById('year').value = '<?php echo addslashes($extractedData['year']); ?>';
            document.getElementById('genre').value = '<?php echo addslashes($extractedData['genre']); ?>';
            document.getElementById('support').value = 'vinyl';
            document.getElementById('vinylForm').submit();
        });
    </script>
    <?php endif; ?>
</body>
</html>
