<?php
class Competition {
    private $connexion;
    private $table = "competitions";

    // object properties
    public $nom;
    public $organisateur;
    public $date_fin;
    public $tier;
    public $format;
    public $statut;

    public function __construct($db) {
        $this->connexion = $db;
    }

    public function createTournoi() {
        $query = "INSERT INTO " . $this->table .  "
            SET
                nom = :nom,
                organisateur = :organisateur,
                date_fin = :date_fin,
                tier = :tier,
                format = :format,
                statut = :statut";

        $stmt = $this->connexion->prepare($query);

        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->organisateur = htmlspecialchars(strip_tags($this->organisateur));
        $this->date_fin = htmlspecialchars(strip_tags($this->date_fin));
        $this->tier = htmlspecialchars(strip_tags($this->tier));
        $this->format = htmlspecialchars(strip_tags($this->format));
        $this->statut = htmlspecialchars(strip_tags($this->statut));

        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':organisateur', $this->organisateur);
        $stmt->bindParam(':date_fin', $this->date_fin);
        $stmt->bindParam(':tier', $this->tier);
        $stmt->bindParam(':format', $this->format);
        $stmt->bindParam(':statut', $this->statut);

        if($stmt->execute()) {
            return $this->connexion->lastInsertId();
        }

        return false;
    }
}