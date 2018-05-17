<?php

class Titre_model extends CI_Model
{
    public function lister($id_albums){
        $this->db->from('titres');
        $this->db->where('id_albums',$id_albums);

        $query = $this->db->get();

        return $query->result('Titre');
    }
}