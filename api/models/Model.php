<?php
namespace Api\Models;

use Api\Database\Database;
use PDO;

abstract class Model {
    protected $db;

    protected $table;
    protected $primaryKey = 'id';
    protected $uniqueField;

    public function __construct() {
        $this->db = Database::getConnection(); 
    }

    /**
     * Récupère toutes les lignes de la table
     */
    public function getAll() {
        $sql = "SELECT * FROM " . $this->table;
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une ligne spécifique par son ID
     */
    public function getById(int $id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insère une nouvelle ligne dynamiquement
     */
    public function create(array $data) {
        $colonnes = implode(', ', array_keys($data));
        $marqueurs = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO " . $this->table . " (" . $colonnes . ") VALUES (" . $marqueurs . ")";
        $query = $this->db->prepare($sql);
        
        if($query->execute($data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Met à jour une ligne dynamiquement
     */
    public function update(int $id, array $data) {
        $champs = [];
        foreach ($data as $colonne => $valeur) {
            $champs[] = $colonne . " = :" . $colonne;
        }
        $listeChamps = implode(', ', $champs);

        $sql = "UPDATE " . $this->table . " SET " . $listeChamps . " WHERE " . $this->primaryKey . " = :id";
        $query = $this->db->prepare($sql);
        
        // On ajoute l'ID dans le tableau de données pour le "bind" final
        $data['id'] = $id;

        return $query->execute($data);
    }

    /**
     * Supprime définitivement une ligne
    */
    public function delete(int $id) {
        $sql = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
        
        $query = $this->db->prepare($sql);
        return $query->execute(['id' => $id]);
    }

    /**
     * Trouve un enregistrement par un champ unique, ou le crée s'il n'existe pas
     */
    public function findOrCreate(array $data) {

        // Sécurité. Si le modèle fille n'a pas défini de champ unique, on bloque.
        if (empty($this->uniqueField)) {
            throw new Exception("La méthode findOrCreate ne peut pas être utilisée sur la table '{$this->table}' car aucun uniqueField n'est défini.");
        }

        // Sécurité. Vérifie que la donnée requise a bien été envoyée.
        if (!isset($data[$this->uniqueField])) {
            throw new Exception("Le champ '{$this->uniqueField}' est requis pour chercher ou créer dans la table '{$this->table}'.");
        }

        $valeurRecherchee = $data[$this->uniqueField];

        $sqlCheck = "SELECT " . $this->primaryKey . " FROM " . $this->table . " WHERE " . $this->uniqueField . " = :val LIMIT 1";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute(['val' => $valeurRecherchee]);

        if($stmtCheck->rowCount() > 0) {
            $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            return $row[$this->primaryKey]; // Renvoie l'ID existant
        }

        return $this->create($data); 
    }
}
?>