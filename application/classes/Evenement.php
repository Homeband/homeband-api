<?php

class Evenement extends MY_Object
{
    public $id_evenements = 0;
    public $nom = '';
    public $description = '';
    public $date_heure = '';
    public $prix = 0.0;
    public $illustration = '';
    public $est_actif = true;
    public $id_groupes = 0;
    public $id_adresses = 0;
}