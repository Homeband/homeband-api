<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 18/12/2017
 * Time: 16:03
 */

class Evenement_model extends CI_Model
{

    public function lister($id_groupes, $date_debut, $date_fin, $qte){
        $this->db->from('groupes_evenements');
        $this->db->where('id_groupes', $id_groupes);
        $this->db->where('est_actif', true);

        if(isset($date_debut)){
            $this->db->where('date_heure >=', $date_debut);
        }

        if(isset($date_fin)){
            $this->db->where('date_heure <=', $date_fin);
        }

        if(isset($qte)){
            $this->db->limit($qte);
        }

        $query = $this->db->get();

        return $query->result('Evenement');
    }

    public function ajouter($event, $id_groupes = 0){

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $event->id_groupes = $id_groupes;
        }


        if($this->db->insert('groupes_evenements', $event)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_event, $id_groupes = 0){
        $this->db->from('groupes_evenements');
        $this->db->where('id_evenements', $id_event);
        $this->db->where('est_actif', true);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        $query = $this->db->get();

        return $query->row(0, 'Evenement');
    }

    public function modifier($event, $id_evenements, $id_groupes = 0){
        $this->db->where('id_evenements', $id_evenements);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        return $this->db->update('groupes_evenements', $event);
    }

    public function supprimer($id_evenements){
        // Préparation de la requête
        $this->db->from('groupes_evenements');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_evenements', $id_evenements);

        return $this->db->update();
    }
}