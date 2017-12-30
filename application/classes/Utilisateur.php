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
    public $api_ck = '';

    public function hash_password(){
        $this->mot_de_passe = password_hash($this->mot_de_passe, PASSWORD_DEFAULT);
    }

    public function check_password($password){
        return password_verify($password, $this->mot_de_passe);
    }
}