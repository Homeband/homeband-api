<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 18/12/2017
 * Time: 16:03
 */

class Evenement_model extends CI_Model
{

    public function lister($id_groupes, $date_debut, $date_fin, $qte, $detail){
        $this->db->from('evenements');
        $this->db->where('id_groupes', $id_groupes);
        $this->db->where('est_actif', true);

        if(isset($date_debut)){
            //$this->db->where('date_heure >=', $date_debut);
        }

        if(isset($date_fin)){
           // $this->db->where('date_heure <=', $date_fin);
        }

        if(isset($qte)){
            $this->db->limit($qte);
        }

        $query = $this->db->get();

        $events = $query->result('Evenement');
        if($detail){
            foreach($events as $event){
                $this->db->from("details_evenements");
                $this->db->where("est_actif", true);
                $this->db->where("id_evenements", $event->id_evenements);

                $query_detail = $this->db->get();
                $event->details = $query_detail->result("EvenementDetail");
            }
        }

        return $events;
    }

    public function ajouter($event, $id_groupes = 0){

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $event->id_groupes = $id_groupes;
        }


        if($this->db->insert('evenements', $event)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_event, $id_groupes = 0){
        $this->db->from('evenements');
        $this->db->where('id_evenements', $id_event);
        $this->db->where('est_actif', true);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        $query = $this->db->get();

        return $query->row(0, 'Evenement');
    }

    public function recuperer_detail($id_event){
        $this->db->from('details_evenements');
        $this->db->where('id_evenements', $id_event);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->result('Detail_Evenement');
    }

    public function modifier($event, $id_evenements, $id_groupes = 0){
        $this->db->where('id_evenements', $id_evenements);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        return $this->db->update('evenements', $event);
    }

    public function supprimer($id_evenements){
    // Préparation de la requête
    $this->db->from('evenements');

    // Modification du statut est_actif à false
    $this->db->set('est_actif', false);
    $this->db->where('id_evenements', $id_evenements);

    return $this->db->update();
}

    public function supprimer_detail($id_evenements){
        // Préparation de la requête
        $this->db->from('details_evenements');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_evenements', $id_evenements);

        return $this->db->update();
    }
}