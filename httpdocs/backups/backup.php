<?php
// Database
$dbHost = 'catalozvinili.mysql.db';
$dbUsername = 'catalozvinili';
$dbPassword = 'Fd883d0ffa';
$dbName = 'catalozvinili';
$backupDatabaseName = 'backup_db.sql';
$timestamp = date('Ymd_His');
// Percorso della root del webserver
$webRootPath = '/home/cataloz';
$backupWebRootName = 'webserver_backup.zip';

// Connessione al database
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Esegui il backup del database
$backupDatabaseCommand = "mysqldump -h$dbHost -u$dbUsername -p$dbPassword $dbName > $backupDatabaseName";
exec($backupDatabaseCommand);

// Comprimi la root del webserver
$backupWebRootCommand = "zip -r $backupWebRootName $webRootPath";
exec($backupWebRootCommand);

// Crea un nuovo zip e aggiungi i file backup
$zip = new ZipArchive();
$zipFileName = "backup_full_$timestamp.zip";

if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
    $zip->addFile($backupDatabaseName);
    $zip->addFile($backupWebRootName);
    $zip->close();

    // Elimina i file di backup singoli
    unlink($backupDatabaseName);
    unlink($backupWebRootName);

    echo 'Backup completato con successo.';
} else {
    echo 'Errore durante la creazione del file ZIP.';
}
file_put_contents('log.txt', ob_get_contents());
?>