<?php

namespace Api\Models;

class EquipeModel extends Model {
    protected $table = 'equipes';
    protected $primaryKey = 'id_equipe';
    protected $uniqueField = 'nom';
}

?>