<?php

class Utilisateur_model extends CI_Model implements JsonSerializable
{
    //Classe private car pas besoin d'être accessible ailleurs + get et set creer dans cette classe
    private static $db;

    private $id_utilisateur=0;
    private $id_adresses=0;
    private $email='';
    private $login='';
    private $mot_de_passe='';
    private $nom='';
    private $prenom='';
    private $est_actif=0;

    public function __construct()
    {
        parent::__construct();
        //self:: = $this sauf qu'on fait référence à l'objet courrant déclarer en static donc pas utilisation this mais self
        // Propre à codeigniter c'est pour loader librairies db en static
        self::$db = &get_instance()->db;
    }

    public static function lister(){
        $users = [];

        $query = self::$db->get('UTILISATEURS');

        foreach ($query->result('Utilisateur_model') as $user){

           $users[] = $user;
        }

        return $users;
    }
    public function inscrire(){
        $data = get_object_vars($this);
        return $this->db->insert('utilisateurs', $data);
    }
    public function connecter(){
        // requête de type where 'login' = 'Chris'
        $this->db->where('login', $this->login);
        $this->db->where('mot_de_passe', $this->mot_de_passe);
        $this->db->where('est_actif', TRUE);
        // Select * from
        $query = $this->db->get("UTILISATEURS");
        //selectionne la première ligne
        $row = $query->row(0, 'Utilisateur_model');

        // Si variable row = à quelque chose
        if(isset($row)) {
            // Connexion réussie
            $this->id_utilisateur = $row->id_utilisateur;
            $this->id_adresses = $row->id_adresses;
            $this->nom = $row->nom;
            $this->prenom = $row->prenom;
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