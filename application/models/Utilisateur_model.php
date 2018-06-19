<?php

class Utilisateur_model extends CI_Model
{
    private $table="utilisateurs";
    public function __construct()
    {
        parent::__construct();
    }

    public function lister($cp, $rayon){
        $this->db->from('utilisateurs');
        $this->db->where('est_actif', true);

        //code posatl dans table ville
        //joindre Ville - utilisateurs et Adresses
        if(isset($cp)){
            $this->db->where('code_postal =', $cp);
        }

        if(isset($rayon)){
            $this->db->where('rayon <=', $rayon);
        }

        $query = $this->db->get();

        return $query->result('Utilisateur');
    }

    public function ajouter($user){
        $user->est_actif = true;
        if($this->db->insert('utilisateurs', $user)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_utilisateurs){
        $this->db->from('utilisateurs');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->row(0, 'Utilisateur');
    }

    public function recuperer_par_email($email){
        $this->db->from('utilisateurs');
        $this->db->where('email', $email);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->row(0, 'Utilisateur');
    }

    public function modifier($user, $id_utilisateurs){

        // Préparation de la requête
        $this->db->where('id_utilisateurs' ,$id_utilisateurs);
        foreach(get_object_vars($user) as $att => $val){
            if(($att != 'api_ck' && $att != 'mot_de_passe' && $att != 'id_utilisateurs') || ($att == 'mot_de_passe' && !empty($val))){
                $this->db->set($att, $val);
            }
        }

        // Modification de la fiche
        return $this->db->update('utilisateurs');
    }

    public function supprimer($id_utilisateurs){
        // Préparation de la requête
        $this->db->from($this->table);

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_utilisateurs', $id_utilisateurs);

        return $this->db->update();
    }

    public function connecter($login, $mot_de_passe){

        // Table
        $this->db->from('utilisateurs');

        // Conditions
        $this->db->where('login', $login);
        $this->db->where('est_actif', TRUE);

        // Execution de la requête
        $query = $this->db->get();

        // Récupération du premier utilisateur
        $user = $query->row(0, 'Utilisateur');

        // Si l'utilisateur a été trouvé et que son mot de passe est correct
        if(isset($user) && $user->check_password($mot_de_passe)) {
            // Génération du CK pour la session
            $ck = random_string('alnum', 48);

            $this->_update_ck($user->id_utilisateurs, $ck);

            $user->api_ck = $ck;
            $user->mot_de_passe = '';

            // Renvoi de l'objet utilisateur correspondant
            return $user;

        } else{
            // Echec de la connexion

            return NULL;
        }
    }

    public function verifie_login($login){
        $this->db->from('utilisateurs');
        $this->db->where('login', $login);
        $this->db->where('est_actif', true);

        return ($this->db->count_all_results() == 0);
    }

    public function verifie_email($email){
        $this->db->from('utilisateurs');
        $this->db->where('email', $email);
        $this->db->where('est_actif', true);

        return ($this->db->count_all_results() == 0);
    }

    public function getLightVersion($id_utilisateurs){
        $this->db->select("id_utilisateurs, login, email, est_actif");
        $this->db->from('utilisateurs');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->row(0, 'Utilisateur');
    }

    private function _update_ck($id_utilisateurs, $ck){
        $this->db->from('utilisateurs');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->set('api_ck', $ck);
        $this->db->update();
    }

}