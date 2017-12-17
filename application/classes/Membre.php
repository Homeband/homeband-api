<?php
class Membre extends MY_Object
{
    public $id_membres = 0;
    public $nom = '';
    public $prenom = '';
    public $date_debut = '';
    public $date_fin = '';
    public $est_date = false;
    public $est_actif = true;
    public $id_groupes = 0;
}