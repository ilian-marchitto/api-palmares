<?php

namespace Api\Models;

class RosterModel extends Model {
    protected $table = 'rosters';
    protected $primaryKey = 'id_roster, id_joueur';
}

?>