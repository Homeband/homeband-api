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
        $order = $this->get('order');

        $villes = $this->villes->lister($cp, $order);


        $results = array(
            'status' => true,
            'villes' => $villes
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function index_post(){
        $ville = new Ville($this->post('ville'));
    }

    public function detail_get($id_villes){
        $ville = $this->villes->recuperer($id_villes);

        if($ville != null){}
        $results = array(
            'status' => true,
            'message' => 'Opération réussie',
            'ville' => $ville
        );

        $this->response($results, REST_Controller::HTTP_OK);
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