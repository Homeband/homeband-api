<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 25/12/2017
 * Time: 20:12
 */

class Utilisateur_groupe_model extends CI_Model
{
    private static $db;

    private $id_utilisateur=0;
    private $id_adresses=0;
    private $email='';
    private $login='';
    private $mot_de_passe='';
    private $nom='';
    private $prenom='';
    private $est_actif=0;

    public function __construct()
    {
        parent::__construct();
        //self:: = $this sauf qu'on fait référence à l'objet courrant déclarer en static donc pas utilisation this mais self
        // Propre à codeigniter c'est pour loader librairies db en static
        self::$db = &get_instance()->db;
    }

    public function lister($cp, $rayon,$qte){
        $this->db->from('');
        $this->db->where();
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

        return $query->result('');
    }
    public function ajouter($groups){

        if($this->db->insert('utilisateur', $groups)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_utilisateurs){
        $this->db->from('utilisateur');
        $this->db->where('id_utilisateurs', $id_utilisateurs);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->row(0, 'Utilisateur');
    }

    public function modifier($user, $id_utilisateurs){
        $this->db->where('id_utilisateurs', $id_utilisateurs);

        return $this->db->update('utilisateur', $user);
    }

    public function supprimer($id_utilisateurs){
        // Préparation de la requête
        $this->db->from('utilisateur');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_utilisateurs', $id_utilisateurs);

        return $this->db->update();
    }

}