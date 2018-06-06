<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 17/12/17
 * Time: 22:04
 */

class Avis_model extends CI_Model
{
    public function lister($id_groupes,$id_utilisateurs, $date_debut, $date_fin, $qte, $type = null){
        $this->db->from('avis');
        $this->db->where('est_actif', true);


        if (isset($id_utilisateurs)&& $id_utilisateurs>0){
            $this->db->where('id_utilisateurs', $id_utilisateurs);
        }

        if (isset($id_groupes)&& $id_groupes>0){
            $this->db->where('id_groupes', $id_groupes);
        }

        if(isset($date_debut)){
            $this->db->where('date_heure >=', $date_debut);
        }

        if(isset($date_fin)){
            $this->db->where('date_heure <=', $date_fin);
        }

        if(isset($type)){
            switch(intval($type)){
                case 1:
                    $this->db->where('est_verifie', true);
                    $this->db->where('est_valide', true);
                    break;
                case 2:
                    $this->db->where('est_verifie', true);
                    $this->db->where('est_valide', false);
                    break;
                case 3:
                    $this->db->where('est_verifie', false);
                    break;
            }
        }

        $this->db->order_by("date_ajout DESC");

        if(isset($qte)){
            $this->db->limit($qte);
        }

        $query = $this->db->get();

        return $query->result('Avis');
    }

    public function ajouter($comment, $id_groupes = 0){

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $comment->id_groupes = $id_groupes;
        }


        if($this->db->insert('avis', $comment)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_avis, $id_groupes = 0){
        $this->db->from('avis');
        $this->db->where('id_avis', $id_avis);
        $this->db->where('est_actif', true);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        $query = $this->db->get();

        return $query->row(0, 'Avis');
    }

    public function modifier($comment, $id_avis, $id_groupes = 0){
        $this->db->where('id_avis', $id_avis);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        return $this->db->update('avis', $comment);
    }

    public function supprimer($id_avis){
        // Préparation de la requête
        $this->db->from('avis');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_avis', $id_avis);

        return $this->db->update();
    }

}