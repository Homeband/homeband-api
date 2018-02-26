<?php

class Ville_model extends CI_Model
{
    private $table = 'villes';

    public function recuperer($id_villes){
        $this->db->from($this->table);
        $this->db->where('id_villes', $id_villes);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->row(0, 'Ville');
    }

    public function lister($cp, $order){
        $this->db->from('villes');
        $this->db->where('est_actif',true);

        if(isset($cp) && $cp > 0){
            $this->db->where('code_postal', $cp);
        }

        if(isset($order)){
            switch($order){
                case "cp" :
                    $this->db->order_by('code_postal', 'ASC');
                    break;
                case "nom" :
                    $this->db->order_by('nom', 'ASC');
            }
        }

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