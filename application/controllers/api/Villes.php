<?php

class Villes extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('ville_model', 'ville');
    }

    public function index_get(){

        $cp = $this->get('cp');

        if($cp <= 0) {
            $villes = $this->ville->listAll();

            $results = array(
                'status' => true,
                'liste' => $villes
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $villes = $this->ville->getByCP($cp);

            $results = array(
                'status' => true,
                'liste' => $villes
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }


    }


}