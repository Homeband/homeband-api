<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 24/02/18
 * Time: 12:19
 */

class Adresses extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('adresse_model', 'adresses');
        $this->load->library("Geocoding");
    }

    public function detail_get($id_adresses){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array()
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        // Traitement de la requête
        $address = $this->adresses->recuperer($id_adresses);


        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'address' => $address
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }


}