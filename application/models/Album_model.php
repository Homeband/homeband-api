<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 17/12/17
 * Time: 22:03
 */

class Album_model extends CI_Model
{

    public function lister($id_groupes, $date_debut, $date_fin, $qte){
        $this->db->from('albums');
        $this->db->where('id_groupes', $id_groupes);
        $this->db->where('est_actif', true);

        if(isset($date_debut)){
            $this->db->where('date_sortie >=', $date_debut);
        }

        if(isset($date_fin)){
            $this->db->where('date_sortie <=', $date_fin);
        }

        if(isset($qte)){
            $this->db->limit($qte);
        }

        $query = $this->db->get();

        return $query->result('Album');
    }

    public function ajouter($album, $id_groupes = 0){

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $album->id_groupes = $id_groupes;
        }


        if($this->db->insert('albums', $album)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_albums, $id_groupes = 0){
        $this->db->from('albums');
        $this->db->where('id_album', $id_albums);
        $this->db->where('est_actif', true);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        $query = $this->db->get();

        return $query->row(0, 'Album');
    }

    public function modifier($album, $id_albums, $id_groupes = 0){
        $this->db->where('id_album', $id_albums);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        return $this->db->update('albums', $album);
    }

    public function supprimer($id_albums){
        // Préparation de la requête
        $this->db->from('albums');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_album', $id_albums);

        return $this->db->update();
    }
}