<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 08/05/2018
 * Time: 16:35
 */
class Adresse_model extends CI_Model{

    public function recuperer($id_adresses){

        $this->db->from("adresses");
        $this->db->select("*");
        $this->db->where("id_adresses",$id_adresses);
        $this->db->where("est_actif",true);

        $query = $this->db->get();
        return $query->row(0, 'Adresse');

    }
}