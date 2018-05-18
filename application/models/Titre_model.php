<?php

class Titre_model extends CI_Model
{
    public function lister($id_albums,$id_groupes){
        $this->db->from('titres');

        if(isset($id_albums)){
            $this->db->where('id_albums',$id_albums);
        }

        if(isset($id_groupes)){
            $this->db->where('id_groupes',$id_groupes);
        }

        $query = $this->db->get();

        return $query->result('Titre');
    }
}