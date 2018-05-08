<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 25/11/2017
 * Time: 16:12
 */

class Groupe_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
    }

    private $table = 'GROUPES';

    public function inscrire($groupe){
        return $this->db->insert('groupes', $groupe);
    }

    private function _update_ck($id_groupes, $ck){
        $this->db->where('id_groupes', $id_groupes);
        $this->db->set('api_ck', $ck);
        $this->db->update('groupes');
    }

    /**
     * Connexion à l'application WEB
     * @param $login Login
     * @param $mot_de_passe Mot de passe
     * @return null Fiche du groupe
     */
    public function connecter($login, $mot_de_passe){
        // requête de type where 'login' = 'Chris'
        $this->db->where('login', $login);
        //$this->db->where('mot_de_passe', $this->mot_de_passe);
        $this->db->where('est_actif', TRUE);
        // Select * from
        $query = $this->db->get("GROUPES");
        //selectionne la première ligne
        $row = $query->row(0, 'Groupe');

        // Si variable row = à quelque chose
        if(isset($row) && $row->check_password($mot_de_passe)) {
            // Connexion réussie

            // Génération du CK pour la session
            $ck = random_string('alnum', 48);

            $this->_update_ck($row->id_groupes, $ck);

            $row->api_ck = $ck;
            $row->mot_de_passe = '';

            //Objet courant va comprendre tout ça donc $user dans controller Welcome sera = à ça
            return $row;

        } else{
            // Echec de la connexion

            return NULL;
        }
    }

    /**
     * Recherche une liste de groupes sur certains critères
     * @param $lat
     * @param $lon
     * @param $rayon
     * @param $styles
     * @return mixed
     */
    public function lister($lat, $lon, $rayon, $styles){

        // Sélection de tous les champs de la table groupes [est_actif = 1]
        $this->db->select('groupes.*');
        $this->db->from('groupes');
        $this->db->where('groupes.est_actif', true);

        // Filtrage sur le rayon
        if(isset($rayon) && $rayon > 0){
            $this->db->join('villes', 'villes.id_villes = groupes.id_villes');
            $distance = 'GetDistance('.$this->db->escape($lat).','.$this->db->escape($lon).', villes.lat, villes.lon)';

            // Sélection de la distance en plus
            $this->db->select($distance . ' as distance');

            // Condition sur le rayon
            $this->db->where($distance . ' <= ' . $rayon);

        }

        // Filtrage sur le style de musique
        if(isset($styles) && intval($styles) > 0){
            $this->db->join('styles', 'styles.id_styles = groupes.id_styles');
            $this->db->where('styles.id_styles' ,$styles);
        }

        $query = $this->db->get();
        return $query->result('Groupe');

    }

    /**
     * Récupère un groupe en fonction de son ID
     * @param $id_groupe
     * @return mixed
     */
    public function recuperer($id_groupe){
        // Préparation de la requête
        $this->db->from('groupes');
        $this->db->where('id_groupes', $id_groupe);
        $this->db->where('est_actif', true);

        // Execution de la requête
        $query = $this->db->get();

        // Récupération et renvoi de la première ligne
        $row = $query->row(0, 'Groupe');
        return $row;
    }

    /**
     * Modifie la fiche d'un groupe
     * @param $groupe
     * @return mixed
     */
    public function modifier($groupe){
        // Préparation de la requête
        $this->db->where('id_groupes' ,$groupe->id_groupes);
        foreach(get_object_vars($groupe) as $att => $val){
            if(($att != 'api_ck' && $att != 'mot_de_passe' && $att != 'id_groupes') || ($att == 'mot_de_passe' && !empty($val))){
                $this->db->set($att, $val);
            }
        }

        // Modification de la fiche
        return $this->db->update('groupes');
    }

    /**
     * Désactive la fiche d'un groupe
     * @param $id_groupe
     * @return mixed
     */
    public function supprimer($id_groupe){
        // Préparation de la requête
        $this->db->from('groupes');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_groupes' ,$id_groupe);

        return $this->db->update();
    }

    /**
     * Vérifie la disponibilité du login
     * @param $login
     * @return bool
     */
    public function verifie_login($login){
        $this->db->from($this->table);
        $this->db->where('login', $login);
        $this->db->where('est_actif', true);

        return ($this->db->count_all_results() == 0);
    }
}