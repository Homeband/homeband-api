<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 25/11/2017
 * Time: 16:12
 */

class Groupe_model extends CI_Model
{
    public $biographie = '';
    public $contacts = '';
    public $email = '';
    public $est_actif = 1;
    public $id_groupes = 0;
    public $id_style = 0;
    public $id_villes = 0;
    public $lien_bandcamp = '';
    public $lien_facebook = '';
    public $lien_instagram = '';
    public $lien_itunes = '';
    public $lien_soundcloud = '';
    public $lien_spotify = '';
    public $lien_twitter = '';
    public $lien_youtube = '';
    public $login = '';
    public $mot_de_passe = '';
    public $nom = '';

    public function inscrire(){
        $data = get_object_vars($this);
        return $this->db->insert('groupes', $data);
    }
    public function connecter(){
        // requête de type where 'login' = 'Chris'
        $this->db->where('login', $this->login);
        $this->db->where('mot_de_passe', $this->mot_de_passe);
        $this->db->where('est_actif', TRUE);
        // Select * from
        $query = $this->db->get("GROUPES");
        //selectionne la première ligne
        $row = $query->row(0, 'Groupe_model');

        // Si variable row = à quelque chose
        if(isset($row)) {
            // Connexion réussie
            foreach(get_object_vars($row) as $key => $value){
                if($key != 'mot_de_passe'){
                    $this->$key = $value;
                }
            }

            $this->mot_de_passe = '';

            //Objet courant va comprendre tout ça donc $user dans controller Welcome sera = à ça
            return TRUE;

        } else{
            // Echec de la connexion

            return FALSE;
        }
    }
}