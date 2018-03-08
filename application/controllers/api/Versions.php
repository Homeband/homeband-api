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
}