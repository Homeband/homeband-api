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


}