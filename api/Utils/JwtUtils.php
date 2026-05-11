<?php
namespace Api\Utils;

class JwtUtils {

    // On récupère la clé secrète depuis l'environnement
    private static function getSecretKey(): string {
        return getenv('JWT_SECRET') ?: 'UnePhraseSecreteTresLongueEtTresComplexeQuePersonneNeDoitConnaitre123!';
    }

    /**
     * Génère un Token JWT valide
     */
    public static function generateToken(array $payload, int $expirationSeconds = 3600): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // On ajoute la date d'expiration au contenu
        $payload['exp'] = time() + $expirationSeconds;
        $payload['iat'] = time(); // Heure de création
        $payload_json = json_encode($payload);

        // Encodage en Base64Url (Format standard pour le web)
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload_json));

        // Création de la signature avec la clé secrète
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Le Token final (les 3 parties séparées par un point)
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Vérifie si un Token JWT est valide et non expiré.
     * Retourne le contenu (payload) si tout est ok, ou false en cas d'erreur.
     */
    public static function validateToken(string $token) {
        // 1. On coupe le jeton en 3 morceaux avec le point (.)
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false; // Jeton mal formaté
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // 2. On recalcule la signature avec NOTRE clé secrète
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // 3. On compare la signature calculée avec celle du jeton
        // hash_equals protège contre les attaques temporelles (Timing Attacks)
        if (!hash_equals($expectedSignature, $base64UrlSignature)) {
            return false; // Quelqu'un a trafiqué le jeton !
        }

        // 4. On décode le contenu (Payload)
        $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload));
        $payload = json_decode($payload_json, true);

        // 5. On vérifie la date d'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Le jeton a expiré
        }

        // Tout est bon, on renvoie les infos de l'utilisateur !
        return $payload;
    }
}
?>