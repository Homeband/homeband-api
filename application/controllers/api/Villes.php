<?php

class Villes extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('ville_model', 'villes');
        $this->load->library('geocoding');
    }

    public function index_get(){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array()
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $cp = $this->get('cp');
        $order = $this->get('order');

        $villes = $this->villes->lister($cp, $order);


        $results = array(
            'status' => true,
            'villes' => $villes
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }
}