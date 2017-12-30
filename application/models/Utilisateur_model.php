<?php

class Utilisateur_model extends CI_Model
{
    private $table="utilisateurs";
    public function __construct()
    {
        parent::__construct();
    }

    public function lister($cp, $rayon){
        $this->db->from('utilisateurs');
        $this->db->where('est_actif', true);

        //code posatl dans table ville
        //joindre Ville - utilisateurs et Adresses
        if(isset($cp)){
            $this->db->where('code_postal =', $cp);
        }

        if(isset($rayon)){
            $this->db->where('rayon <=', $rayon);
        }

        $query = $this->db->get();

        return $query->result('Utilisateur');
    }

    public function ajouter($user){
        if($this->db->insert('utilisateurs', $user)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_utilisateurs){
        $this->db->from('utilisateurs');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->row(0, 'Utilisateur');
    }

    public function modifier($user, $id_utilisateurs){
        $this->db->where('id_utilisateurs', $id_utilisateurs);

        return $this->db->update('utilisateurs', $user);
    }

    public function supprimer($id_utilisateurs){
        // Préparation de la requête
        $this->db->from($this->table);

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_utilisateurs', $id_utilisateurs);

        return $this->db->update();
    }

    public function connecter($login, $mot_de_passe){

        $this->db->from('utilisateurs');
        // requête de type where 'login' = 'Chris'
        $this->db->where('login', $login);
        //$this->db->where('mot_de_passe', $this->mot_de_passe);
        $this->db->where('est_actif', TRUE);
        // Select * from
        $query = $this->db->get();
        //selectionne la première ligne
        $row = $query->row(0, 'Utilisateur');

        // Si variable row = à quelque chose
        if(isset($row) && $row->check_password($mot_de_passe)) {
            // Connexion réussie

            // Génération du CK pour la session
            $ck = random_string('alnum', 48);

            $this->_update_ck($row->id_utilisateurs, $ck);

            $row->api_ck = $ck;
            $row->mot_de_passe = '';

            //Objet courant va comprendre tout ça donc $user dans controller Welcome sera = à ça
            return $row;

        } else{
            // Echec de la connexion

            return NULL;
        }
    }

    private function _update_ck($id_utilisateurs, $ck){
        $this->db->from('utilisateurs');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->set('api_ck', $ck);
        $this->db->update();
    }

}