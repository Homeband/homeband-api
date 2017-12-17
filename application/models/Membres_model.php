<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 17/12/2017
 * Time: 16:30
 */

class Membres_model extends CI_Model
{
    public $date_debut='';
    public $date_fin='';
    public $est_actif=0;
    public $est_date=false;
    public $id_groupes=0;
    public $id_membres=0;
    public $nom='';
    public $prenom='';

    public function lister ($date_debut, $date_fin,$qte,$id_groupe){
        $this->db->select('membres_groupes.*');
        $this->db->from('membres_groupes');
        $this->db->where('est_actif' ,true);
        if(isset($id_groupe)){
            $this->db->where('id_groupes' ,$id_groupe);
    }

        if(isset($date_debut)){
            $this->db->where('date_debut <=' ,$date_debut);
        }
        if(isset($date_fin)){
            $this->db->where('date_fin >=' ,$date_fin);
        }

        if(isset($qte) && is_numeric($qte)){
            $this->db->limit($qte);
        }

        $query = $this->db->get();
        return $query->result();
    }


    public function ajouter ($member){
       if ($this->db->insert('membres_groupes', $member)){
           return $this->db->insert_id();
       }else {return 0;}
    }

    public function recuperer ($id,$id_groupe=0){
        $this->db->select('membres_groupes.*');
        $this->db->from('membres_groupes');
        $this->db->where('est_actif' ,true);
        $this->db->where('id_membres' ,$id);
        if($id_groupe>0){
            $this->db->where('id_groupes' ,$id_groupe);
        }
        $query = $this->db->get();
        $row = $query->row(0, 'Membres_model');
        return $row;
    }

    public function modifier($membre){
        $this->db->where('id_membres' ,$membre->id_membres);
        return $this->db->update('membres_groupes', $membre);
    }

    public function supprimer($id_membres){
        $this->db->where('id_membres' ,$id_membres);
        return $this->db->delete('membres_groupes');
    }

}