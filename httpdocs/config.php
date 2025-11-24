<?php

// Usa variabili d'ambiente se disponibili, altrimenti usa i valori di default
$servername = getenv('DB_HOST') ?: "localhost";

$username = getenv('DB_USER') ?: "catalogo";

$password = getenv('DB_PASSWORD') ?: "YOUR_DB_PASSWORD";

$dbname = getenv('DB_NAME') ?: "adri641_catalogo";

// OpenAI API Key
// TODO: Replace with your actual API key or set the OPENAI_API_KEY environment variable
$openaiApiKey = getenv('OPENAI_API_KEY') ?: "YOUR_OPENAI_API_KEY";



// Crea una connessione al database

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");


// Verifica la connessione

if ($conn->connect_error) {

    die("Connessione al database fallita: " . $conn->connect_error);

}

?>