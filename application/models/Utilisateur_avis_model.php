<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 29/12/2017
 * Time: 21:53
 */

class Utilisateur_avis_model extends CI_Model
{
    public function lister($id_utilisateurs, $date_debut, $date_fin, $qte){
        $this->db->from('avis');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
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

        return $query->result('Annonce');
    }

    public function ajouter($annonce, $id_groupes = 0){

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $annonce->id_groupes = $id_groupes;
        }


        if($this->db->insert('annonces', $annonce)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_annonces, $id_groupes = 0){
        $this->db->from('annonces');
        $this->db->where('id_annonces', $id_annonces);
        $this->db->where('est_actif', true);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        $query = $this->db->get();

        return $query->row(0, 'Annonce');
    }

    public function modifier($annonce, $id_annonces, $id_groupes = 0){
        $this->db->where('id_annonces', $id_annonces);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        return $this->db->update('annonces', $annonce);
    }

    public function supprimer($id_annonces){
        // Préparation de la requête
        $this->db->from('annonces');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_annonces', $id_annonces);

        return $this->db->update();
    }
}