<?php
// Autorisations CORS (Indispensables pour qu'Angular puisse lire cette API)
header("Access-Control-Allow-Origin: *"); // En production, remplacer * par l'URL de ton site Angular
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

class Database {
    private static $instance = null;

    private function __construct() {}

    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            $db   = getenv('DB_NAME') ?: 'nom_de_ta_base';
            $user = getenv('DB_USER') ?: 'root';
            $pwd  = getenv('DB_PASS') ?: '';

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
                self::$instance = new PDO($dsn, $user, $pwd);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch(PDOException $exception) {
                http_response_code(500);
                echo json_encode(["erreur" => "Erreur de connexion BDD : " . $exception->getMessage()]);
                exit; 
            }
        }
        return self::$instance;
    }
}
?>