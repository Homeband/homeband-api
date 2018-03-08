<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 08-03-18
 * Time: 14:53
 */

class Version_model extends CI_Model
{
    public function lister(){
        $this->db->from('versions');
        $query = $this->db->get();

        return $query->result('Version');
    }

    public function recuperer($id_versions){
        $this->db->from('versions');
        $this->db->where('id_versions', $id_versions);

        $query = $this->db->get();

        return $query->row(0, 'Version');
    }

    public function recupererParNum($num){
        $this->db->from('versions');
        $this->db->where('num_table', $num);

        $query = $this->db->get();

        return $query->row(0, 'Version');
    }

    public function recupererParNom($nom){
        $this->db->from('versions');
        $this->db->where('nom_table', $nom);

        $query = $this->db->get();

        return $query->row(0, 'Version');
    }
}