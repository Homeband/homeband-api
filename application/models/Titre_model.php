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

    public function recuperer($id){
        $this->db->from("titres");
        $this->db->where("id_titres", $id);
        $this->db->where("est_actif", true);

        return $this->db->get()->row(0, "Titre");
    }

    public function ajouter($titre){
        $titre->est_actif = true;
        $titre->id_titres = 0;
        if($this->db->insert('titres', $titre)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function modifier($titre){
        // Préparation de la requête
        $this->db->where('id_titres' ,$titre->id_titres);

        foreach(get_object_vars($groupe) as $att => $val){
            $this->db->set($att, $val);
        }

        // Modification de la fiche
        return $this->db->update('titres');
    }

    public function supprimer($titre){

    }
}