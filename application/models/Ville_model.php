<?php

class Ville_model extends CI_Model implements JsonSerializable
{
    private $code_postal;
    private $est_actif;
    private $id_villes;
    private $nom;


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

    public function getByCP($cp){
        $this->db->where('code_postal', $cp);
        $this->db->where('est_actif', true);
        $query = $this->db->get('villes');
        $villes = $query->result('Ville_model');

        return $villes;
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