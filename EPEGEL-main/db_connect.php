<?php
// Fichier: db_connect.php

// Paramètres de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "epegl_db";

// Activer le rapport d'erreurs pour mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Création de la connexion
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Définir le jeu de caractères en UTF-8
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // En cas d'échec de connexion, arrêter le script et renvoyer une erreur JSON
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()
    ]);
    exit();
}
?>