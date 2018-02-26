<?php

class Groupe extends MY_Object
{
    public $id_groupes = 0;
    public $email = '';
    public $login = '';
    public $mot_de_passe = '';
    public $nom = '';
    public $biographie = '';
    public $contacts = '';
    public $lien_bandcamp = '';
    public $lien_facebook = '';
    public $lien_instagram = '';
    public $lien_itunes = '';
    public $lien_soundcloud = '';
    public $lien_spotify = '';
    public $lien_twitter = '';
    public $lien_youtube = '';
    public $date_maj = '';
    public $est_actif = true;
    public $id_styles = 0;
    public $id_villes = 0;
    public $api_ck = '';

    public function hash_password(){
        $this->mot_de_passe = password_hash($this->mot_de_passe, PASSWORD_DEFAULT);
    }

    public function check_password($password){
        return password_verify($password, $this->mot_de_passe);
    }
}