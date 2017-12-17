<?php

class Utilisateur extends MY_Object
{
    public $id_utilisateurs = 0;
    public $email = '';
    public $login = '';
    public $mot_de_passe = '';
    public $nom = '';
    public $prenom = '';
    public $est_actif = true;
}