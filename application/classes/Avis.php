<?php

class Avis extends MY_Object
{
    public $id_avis = 0;
    public $commentaire = '';
    public $date_ajout = '';
    public $date_validation = '';
    public $est_accepte = false;
    public $est_verifie = false;
    public $est_actif = true;
    public $id_groupes = 0;
    public $id_utilisateurs = 0;
}