<?php
namespace Api\Controllers;

use Api\Models\UtilisateurModel;
use Exception;
use Api\Utils\JwtUtils;

class AuthController extends BaseController {

    public function register() {
        // Récupération des données
        $data = $this->getJsonInput();

        // Validation de base (Présence des champs essentiels : identifiant, email et mot de passe)
        if (empty($data['identifiant']) || empty($data['email']) || empty($data['mot_de_passe'])) {
            $this->sendError("Tous les champs (identifiant, email, mot de passe) sont requis.", 400);
        }

        // Vérification du format de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->sendError("Le format de l'adresse email est invalide.", 400);
        }

        $utilisateurModel = new UtilisateurModel();

        try {
            // Vérification des doublons
            if ($utilisateurModel->findByUsername($data['identifiant'])) {
                // Code 409 = Conflict (Ressource déjà existante)
                $this->sendError("Cet identifiant est déjà utilisé.", 409); 
            }
            if ($utilisateurModel->findByEmail($data['email'])) {
                $this->sendError("Cette adresse email est déjà associée à un compte.", 409);
            }

            // Hachage du mot de passe
            $motDePasseHache = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

            // Création de l'utilisateur
            $id_utilisateur = $utilisateurModel->create([
                'identifiant' => $data['identifiant'],
                'email' => $data['email'],
                'mot_de_passe' => $motDePasseHache
                // Le rôle 'Visiteur' sera mis automatiquement grâce au DEFAULT en SQL
            ]);

            if (!$id_utilisateur) {
                throw new Exception("Échec de l'insertion en base de données.");
            }

            // Réponse finale
            $this->sendJson([
                'statut' => 'succès',
                'message' => 'Utilisateur créé avec succès ! Vous pouvez maintenant vous connecter.',
                'id_utilisateur' => $id_utilisateur
            ], 201); // 201 = Created

        } catch (Exception $e) {
            $this->sendError("Erreur serveur lors de la création de l'utilisateur.", 500);
        }
    }
    
    public function login() {
        // Récupération des données
        $data = $this->getJsonInput();

        // Validation de base (Présence des champs essentiels : identifiant/email et mot de passe)
        if (empty($data['login']) || empty($data['mot_de_passe'])) {
            $this->sendError("Le champ de connexion (identifiant ou email) et le mot de passe sont requis.", 400);
        }

        try {
            $utilisateurModel = new UtilisateurModel();
            $utilisateur = null;

            // Vérification du format du champ de connexion pour déterminer s'il s'agit d'un email ou d'un identifiant
            if (filter_var($data['login'], FILTER_VALIDATE_EMAIL)) {
                // C'est un email
                $utilisateur = $utilisateurModel->findByEmail($data['login']);
            } else {
                // Ce n'est pas un email, c'est donc un identifiant
                $utilisateur = $utilisateurModel->findByUsername($data['login']);
            }

            // Si aucun utilisateur n'est trouvé
            if (!$utilisateur) {
                // On met un délai artificiel d'une seconde pour ralentir les attaques par force brute
                sleep(1); 
                $this->sendError("Identifiant, email ou mot de passe incorrect.", 401);
            }

            // Vérification du mot de passe
            if (!password_verify($data['mot_de_passe'], $utilisateur['mot_de_passe'])) {
                sleep(1);
                $this->sendError("Identifiant, email ou mot de passe incorrect.", 401);
            }

            // On met les infos de l'utilisateur dans le token (payload)
            $tokenPayload = [
                'id_utilisateur' => $utilisateur['id_utilisateur'],
                'role' => $utilisateur['role']
            ];

            // Le token est valable 24h (86400 secondes)
            $token = JwtUtils::generateToken($tokenPayload, 86400);

            // Réponse finale
            $this->sendJson([
                'statut' => 'succès',
                'message' => 'Connexion réussie !',
                'id_utilisateur' => $utilisateur['id_utilisateur'],
                'role' => $utilisateur['role'],
                'token' => $token // Angular stockera ce jeton !
            ], 200);

        } catch (Exception $e) {
            $this->sendError("Erreur serveur lors de la connexion.", 500);
        }
    }

}