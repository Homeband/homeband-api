<?php

class Ville_model extends CI_Model
{
    public $code_postal;
    public $est_actif;
    public $id_villes;
    public $nom;


    public function getByCodePostal(){
        $this->db->where('code_postal',$this->code_postal);
        $this->db->where('est_actif',true);
        $query = $this->db->get('villes');
        $villes = $query->result('Ville_model');

        return $villes;
    }

    public function listAll(){
        $this->db->where('est_actif',true);
        $query = $this->db->get('villes');
        $villes = $query->result('Ville_model');

        return $villes;
    }

    public function lister(){
        $this->db->from('villes');
        $this->db->where('est_actif',true);

        $query = $this->db->get();

        return $query->result('Ville');
    }

    public function getByCP($cp){
        $this->db->where('code_postal', $cp);
        $this->db->where('est_actif', true);
        $query = $this->db->get('villes');
        $villes = $query->result('Ville_model');

        return $villes;
    }

    public function ajouter($ville){
        return $this->db->insert("villes", $ville);
    }

    public function modifier($ville){
        $this->db->where('id_villes', $ville->id_villes);

        return $this->db->update('villes', $ville);
    }

}