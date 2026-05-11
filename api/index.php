<?php 

use Api\Controllers\AuthController;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

// On récupère l'URL demandée par Angular (ex: "/api/register")
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($requestUri) {

    // --- ROUTES D'AUTHENTIFICATION ---
    case '/api/register':
        if ($method === 'POST') {
            $controller = new AuthController();
            $controller->register();
        } else {
            http_response_code(405); echo json_encode(["message" => "Méthode non autorisée"]);
        }
        break;

    case '/api/login':
        if ($method === 'POST') {
            $controller = new AuthController();
            $controller->login();
        }
        break;

    // // --- ROUTES DU WIZARD (Tournois) ---
    // case '/api/wizard/create':
    //     if ($method === 'POST') {
    //         $controller = new WizardController();
    //         $controller->createTournamentFull();
    //     }
    //     break;

    // --- ROUTE NON TROUVÉE (404) ---
    // code 404 = "Not Found"
    default:
        http_response_code(404);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(["message" => "Route introuvable : " . $requestUri]);
        break;
}