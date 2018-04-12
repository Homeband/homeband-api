<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 18/12/2017
 * Time: 16:03
 */

class Evenement_model extends CI_Model
{

    public function lister($id_groupes, $date_debut, $date_fin, $qte, $lat, $lon, $rayon, $styles){

        $this->db->select('evenements.*');
        $this->db->from('evenements');
        $this->db->where('evenements.est_actif', true);

        $this->db->join('groupes', 'groupes.id_groupes = evenements.id_groupes');

        // Filtrage sur le groupe
        if(isset($id_groupes)){
            $this->db->where('id_groupes', $id_groupes);
        }

        // Filtrage sur les dates
        if(isset($date_debut) || isset($date_fin)){
            if(isset($date_debut)){
                $this->db->where('evenements.date_heure >=', $date_debut);
            }

            if(isset($date_fin)){
                $datetime_fin = new DateTime($date_fin);
                $datetime_fin->setTime(23,59,59);
                $this->db->where('evenements.date_heure <=', $datetime_fin->format('Y-m-d H:i:s'));
            }
        }

        // Filtrage sur le rayon
        if(isset($rayon)){

            $this->db->join('adresses', 'adresses.id_adresses = evenements.id_adresses');
            $this->db->join('villes', 'villes.id_villes = adresses.id_villes');

            $distance = 'GetDistance(' . $this->db->escape($lat) . ', ' . $this->db->escape($lon) . ', villes.lat, villes.lon)';

            // Sélection de la distance en plus
            $this->db->select($distance . ' as distance');
            $this->db->where($distance .' <= ' . $rayon);
        }

        // Filtrage sur le style de musique
        if(isset($styles)){

            $this->db->join('styles', 'styles.id_styles = groupes.id_styles');
            $this->db->where('styles.id_styles' ,$styles);
        }

        // Limiter la quantité d'éléments
        if(isset($qte)){
            $this->db->limit($qte);
        }

        //die($this->db->get_compiled_select());
        $query = $this->db->get();

        $events = $query->result('Evenement');

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