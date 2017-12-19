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

        $cp = $this->get('cp');

        if($cp <= 0) {
            $villes = $this->villes->lister();

            $results = array(
                'status' => true,
                'liste' => $villes
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $villes = $this->villes->getByCP($cp);

            $results = array(
                'status' => true,
                'liste' => $villes
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function index_post(){
        $villes = $this->post('villes');
        $new_villes = array();
        foreach($villes as $ville) {
            $ville = arrayToObject($ville);
            set_time_limit(300);
            //$tab = array()$this->villes->getByCp($ville->zip);
            //if (empty($tab)) {
                $ma_ville = new Ville();
                $ma_ville->code_postal = $ville->zip;
                $ma_ville->nom = $ville->city;
                $ma_ville->lat = $ville->lat;
                $ma_ville->lon = $ville->lng;
                $ma_ville->est_actif = true;

                $this->villes->ajouter($ma_ville);
                $new_villes[] = $ma_ville;
            //}
        }

        $this->response($new_villes, REST_Controller::HTTP_OK);
    }

    public function geo_get(){
        $villes = $this->villes->lister();
        $new_villes = array();

        foreach($villes as $ville){
            set_time_limit(300);
            $address = "$ville->code_postal $ville->nom Belgium";
            $coord = $this->geocoding->get($address);

            if($coord['lat'] > 0 && $coord['lon'] > 0) {
                $ville->lat = $coord['lat'];
                $ville->lon = $coord['lon'];
            }
            $this->villes->modifier($ville);
            $new_villes[] = $ville;
        }

        $this->response($new_villes, REST_Controller::HTTP_OK);
    }




}