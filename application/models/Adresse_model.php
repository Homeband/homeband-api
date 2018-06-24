<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 08/05/2018
 * Time: 16:35
 */
class Adresse_model extends CI_Model{

    public function __construct()
    {
        $this->load->library("Geocoding");
        $this->load->model('Ville_model', "villes");
    }

    public function recuperer($id_adresses){

        $this->db->from("adresses");
        $this->db->select("*");
        $this->db->where("id_adresses",$id_adresses);
        $this->db->where("est_actif",true);

        $query = $this->db->get();
        return $query->row(0, 'Adresse');

    }

    public function ajouter($adresse){
        $adresse->id_adresses = 0;

        // Géolocalisation
        $ville = $this->villes->recuperer($adresse->id_villes);
        if($ville != null){
            $addresseTxt = "$adresse->rue $adresse->numero $ville->code_postal $ville->nom Belgique";
            $coord = $this->geocoding->getCoordFromAddress($addresseTxt);

            $adresse->lat = $coord['lat'];
            $adresse->lon = $coord['lon'];
        }

        if($this->db->insert('adresses', $adresse)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }
}