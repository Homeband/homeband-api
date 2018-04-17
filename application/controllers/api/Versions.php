<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 08-03-18
 * Time: 14:51
 */

class Versions extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('version_model', 'versions');
    }

    public function index_get(){
        $num_table = $this->get('numtable');
        $nom_table = strtoupper($this->get('nomtable'));

        if(isset($num_table) && ((int) $num_table) > 0){
            $version = $this->versions->recupererParNum($num_table);
            if(isset($version)){
                $results = array(
                    "status" => true,
                    "message" => "Opération réussie",
                    "version" => $version
                );
                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    "status" => false,
                    "message" => "Pas de version correspondant au numéro de table " . $num_table
                );
                $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
            }
        } else if(isset($nom_table) && !empty($nom_table)){
            $version = $this->versions->recupererParNom($nom_table);
            if(isset($version)){
                $results = array(
                    "status" => true,
                    "message" => "Opération réussie",
                    "version" => $version
                );
                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    "status" => false,
                    "message" => "Pas de version correspondant au nom de table \"" . $nom_table . "\""
                );
                $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $versions = $this->versions->lister();
            $results = array(
                "status" => true,
                "message" => "Opération réussie",
                "versions" => $versions
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function updates_post(){
        $versions = $this->post('versions');
        $versions_retour = array();

        if(isset($versions) && is_array($versions) && !empty($versions)){
            foreach($versions as $version_param){
                $version = new Version($version_param);
                $date = new DateTime($version->date_maj);

                // Récupération des informations dans la DB centrale
                $version_db = $this->versions->recupererParNom(strtoupper($version->nom_table));
                if($version_db != null){
                    $date_db = new DateTime($version_db->date_maj);

                    // Vérification de la différence entre les dates
                    if($date_db > $date){
                        $versions_retour[] = $version_db;
                    }
                } else {
                    $results = array(
                        "status" => false,
                        "message" => "La table $version->nom_table n'existe pas sur le serveur"
                    );
                    $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
                }
            }

            $results = array(
                "status" => true,
                "message" => "Opération réussie",
                "maj_dispo" => !(empty($versions_retour)) ,
                "versions" => $versions_retour
            );
            $this->response($results, REST_Controller::HTTP_OK);

        } else {
            $results = array(
                "status" => false,
                "message" => "Un tableau de versions doit être passé en paramètre d'appel."
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}