<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 17/12/2017
 * Time: 16:30
 */

class Membre_model extends CI_Model
{
    /**
     * Liste les membres d'un ou des groupe(s)
     * @param $date_debut
     * @param $date_fin
     * @param $qte
     * @param $id_groupe
     * @return mixed
     */
    public function lister($date_debut, $date_fin, $qte, $id_groupe){
        $this->db->from('membres');
        $this->db->where('est_actif' ,true);
        if(isset($id_groupe)){
            $this->db->where('id_groupes' ,$id_groupe);
    }

        if(isset($date_debut)){
            $this->db->where('date_debut <=' ,$date_debut);
        }
        if(isset($date_fin)){
            $this->db->where('date_fin >=' ,$date_fin);
        }

        if(isset($qte) && is_numeric($qte)){
            $this->db->limit($qte);
        }

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Ajoute un membre
     * @param $membre
     * @return int
     */
    public function ajouter($membre){
       if ($this->db->insert('membres', $membre)){
           return $this->db->insert_id();
       } else {
           return 0;
       }
    }

    /**
     * Récupère la fiche d'un membre
     * @param $id
     * @param int $id_groupe
     * @return mixed
     */
    public function recuperer($id, $id_groupe=0){
        $this->db->from('membres');
        $this->db->where('est_actif', true);
        $this->db->where('id_membres', $id);

        if($id_groupe>0){
            $this->db->where('id_groupes' ,$id_groupe);
        }

        $query = $this->db->get();
        $row = $query->row(0, 'Membre');

        return $row;
    }

    /**
     * Modifie les informations d'un membre
     * @param $membre
     * @return mixed
     */
    public function modifier($membre){
        $this->db->where('id_membres' ,$membre->id_membres);
        return $this->db->update('membres', $membre);
    }

    /**
     * Désactive la fiche d'un membre
     * @param $id_membres
     * @return mixed
     */
    public function supprimer($id_membres){
        // Préparation de la requête
        $this->db->from('membres');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_membres' ,$id_membres);

        return $this->db->update();
    }

}