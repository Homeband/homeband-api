<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 25/11/2017
 * Time: 16:12
 */

class Groupe_model extends CI_Model implements JsonSerializable
{
    private $biographie = '';
    private $contacts = '';
    private $email = '';
    private $est_actif = 1;
    private $id_groupes = 0;
    private $id_style = 0;
    private $id_villes = 0;
    private $lien_bandcamp = '';
    private $lien_facebook = '';
    private $lien_instagram = '';
    private $lien_itunes = '';
    private $lien_soundcloud = '';
    private $lien_spotify = '';
    private $lien_twitter = '';
    private $lien_youtube = '';
    private $login = '';
    private $mot_de_passe = '';
    private $nom = '';

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
            $this->id_utilisateur = $row->id_utilisateur;
            $this->nom = $row->nom;
            $this->email = $row->email;
            $this->est_actif = $row->est_actif;
            //Objet courant va comprendre tout ça donc $user dans controller Welcome sera = à ça
            return TRUE;

        } else{
            // Echec de la connexion

            return FALSE;
        }
    }

    public function __set($key, $value){
        if(property_exists($this, $key)){
            $this->$key = $value;
        }
    }

    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }


}