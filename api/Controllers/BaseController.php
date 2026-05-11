<?php

namespace Api\Controllers;

abstract class BaseController {


    protected function getJsonInput(): array {
        $input = file_get_contents("php://input");
        $decoded = json_decode($input, true);
        
        // Si le JSON est invalide ou vide, on renvoie un tableau vide
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Envoie la réponse finale au format JSON avec le bon code de statut HTTP.
     */
    protected function sendJson(mixed $data, int $statusCode = 200): void {
        // Nettoyage : On vide tout ce qui aurait pu être affiché avant
        if (ob_get_length()) {
            ob_clean();
        }

        // Les Headers CORS et Content-Type
        header_remove(); 
        header("Access-Control-Allow-Origin: *"); // À restreindre en prod
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Content-Type: application/json; charset=UTF-8");

        // Définition du code HTTP (ex: 
            // 400 pour mauvaise requête, 
            // 401 pour non autorisé, 
            // 403 pour interdit,
            // 404 pour non trouvé, 
            // 201 pour créé, 200 pour succès, 
            // 500 pour erreur serveur, 
            // 501 pour non implémenté,
            // 502 pour erreur de passerelle,
            // 503 pour service indisponible,  
            // 504 pour timeout,  
            // etc.)
        http_response_code($statusCode);

        // Conversion et envoi
        echo json_encode($data);
        
        // On arrête le script pour être sûr que rien d'autre ne s'affiche
        exit;
    }

    /**
     * Raccourci pour renvoyer une erreur standardisée
     */
    protected function sendError(string $message, int $statusCode = 400): void {
        $this->sendJson([
            'statut' => 'erreur',
            'message' => $message
        ], $statusCode);
    }
}