<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 26-12-17
 * Time: 22:44
 */

class Style_model extends CI_Model
{
    public function lister(){
        $this->db->from('styles');
        $this->db->where('est_actif',true);

        $query = $this->db->get();

        return $query->result('Style');
    }
}