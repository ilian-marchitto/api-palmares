<?php

namespace Api\Models;

Use PDO;

class UtilisateurModel extends Model {
    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    public function findByUsername(string $identifiant) {
        $sql = "SELECT * FROM " . $this->table . " WHERE identifiant = :identifiant";

        $query = $this->db->prepare($sql);
        $query->execute(['identifiant' => $identifiant]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email) {
        $sql = "SELECT * FROM " . $this->table . " WHERE email = :email";

        $query = $this->db->prepare($sql);
        $query->execute(['email' => $email]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}

?>