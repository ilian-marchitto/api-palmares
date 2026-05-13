<?php

namespace Api\Controllers;

/**
 * Contrôleur abstrait principal (BaseController).
 * Fournit le socle commun à tous les contrôleurs de l'API.
 * Il centralise la lecture des requêtes entrantes, la sécurisation des réponses (CORS, JSON),
 * et agit comme un pare-feu d'authentification (Middleware) via JWT.
 * * @package Api\Controllers
 */
abstract class BaseController {

    /**
     * Lit et décode le corps (payload) de la requête HTTP entrante.
     *
     * @return array Les données décodées sous forme de tableau associatif. Retourne un tableau vide si le JSON est absent ou invalide.
     */
    protected function getJsonInput(): array {
        $input = file_get_contents("php://input");
        $decoded = json_decode($input, true);
        
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Formate et expédie une réponse HTTP propre au format JSON, puis arrête le script.
     * Cette méthode garantit l'intégrité de l'API : elle nettoie les tampons de sortie
     * pour éviter que des avertissements PHP (Warnings) ne viennent corrompre le format JSON renvoyé au client.
     *
     * @param mixed $data       Les données à sérialiser.
     * @param int   $statusCode Le code d'état HTTP de la réponse (ex: 200 OK, 201 Created, 404 Not Found). Défaut: 200.
     * @return void Cette méthode termine l'exécution du script (exit) et ne retourne rien.
     */
    protected function sendJson(mixed $data, int $statusCode = 200): void {
        // Nettoyage des tampons pour garantir un JSON pur
        if (ob_get_length()) {
            ob_clean();
        }

        // Configuration des en-têtes (CORS et Type de contenu)
        header_remove(); 
        header("Access-Control-Allow-Origin: *"); 
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Content-Type: application/json; charset=UTF-8");

        http_response_code($statusCode);

        echo json_encode($data);
        exit;
    }

    /**
     * Raccourci utilitaire pour renvoyer une erreur standardisée.
     * Construit une structure JSON prévisible `{"statut": "erreur", "message": "..."}` 
     * et envoie une réponse HTTP avec le code d'erreur approprié.
     *
     * @param string $message    Le message d'erreur explicite à afficher à l'utilisateur.
     * @param int    $statusCode Le code d'état HTTP d'erreur (ex: 400 Bad Request, 401 Unauthorized). Défaut: 400.
     * @return void
     */
    protected function sendError(string $message, int $statusCode = 400): void {
        $this->sendJson([
            'statut' => 'erreur',
            'message' => $message
        ], $statusCode);
    }

    /**
     * Pare-feu d'authentification : valide le jeton JWT de la requête courante.
     * Lit l'en-tête HTTP `Authorization`, extrait le jeton "Bearer" et valide sa signature cryptographique.
     * Si l'utilisateur n'est pas connecté, le jeton manquant ou expiré, la requête est instantanément 
     * rejetée avec une erreur 401.
     *
     * @return array Le payload du jeton (contenant généralement 'id_utilisateur' et 'role').
     * Note : Ne retourne jamais null, car le script est interrompu en cas d'échec.
     */
    protected function requireAuth(): array {
        $headers = apache_request_headers();
        
        $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->sendError("Accès refusé. Jeton d'authentification manquant ou mal formaté.", 401);
        }

        $token = $matches[1];
        $payload = \Api\Utils\JwtUtils::validateToken($token);

        if (!$payload) {
            $this->sendError("Accès refusé. Jeton invalide, trafiqué ou expiré.", 401);
        }

        return $payload;
    }
}