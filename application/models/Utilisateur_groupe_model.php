<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 25/12/2017
 * Time: 20:12
 */

class Utilisateur_groupe_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function lister($id_utilisateurs,$cp, $rayon,$qte){
        $this->db->select('groupes.*');
        $this->db->from('utilisateurs_groupes');
        $this->db->where("id_utilisateurs",$id_utilisateurs);
        $this->db->where('est_actif', true);
        $this->db->join('groupes', 'groupes.id_groupes = utilisateurs_groupes.id_groupes');
        if(isset($cp)){
            $this->db->where('code_postal =', $cp);
        }

        if(isset($rayon)){
            $this->db->where('rayon <=', $rayon);
        }
        if(isset($qte)){
        $this->db->limit($qte);
        }
        $query = $this->db->get();

        return $query->result('Groupe');
    }
    public function ajouter($id_utilisateurs,$groups){
        $data=array(
            "id_groupes" =>$groups,
            "id_utilisateurs" => $id_utilisateurs
        );
        return ($this->db->insert('utilisateurs_groupes', $data));
    }

    public function recuperer($id_utilisateurs,$id_groupes){
        $this->db->select('*');
        $this->db->from('utilisateurs_groupes');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->where('id_groupes',$id_groupes);

        $query = $this->db->get();

        return $query->row(0, 'UtilisateurGroupe');
    }

    public function supprimer($id_utilisateurs,$id_groupes){
        // Préparation de la requête
        $this->db->from('utilisateurs_groupes');

        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->where('id_groupes', $id_groupes);

        return $this->db->delete();
    }



}