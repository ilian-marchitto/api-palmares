<?php

namespace Api\Models;

class JoueurModel extends Model {
    protected $table = 'joueurs';
    protected $primaryKey = 'id_joueur';
    protected $uniqueField = 'nickname';
}

?>