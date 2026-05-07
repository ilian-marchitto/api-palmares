<?php
// Ajuste le chemin relatif selon l'endroit où tu as placé database.php
require_once 'config/database.php';

// Appel de la connexion
$pdo = Database::getConnection();

// Si on arrive ici sans que le "catch" de database.php n'ait stoppé le script, c'est un succès.
if ($pdo) {
    http_response_code(200);
    echo json_encode([
        "statut" => "succès",
        "message" => "Connexion à la base de données réussie avec succès !"
    ]);
}
?>