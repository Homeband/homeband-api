<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 27-03-18
 * Time: 16:10
 */

class Localisations extends REST_Controller
{
    const TYPE_COORD_ADDRESS = 1;
    const TYPE_ADDRESS_COORD = 2;

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->library('Geocoding');
    }

    public function index_get(){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array()
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Récupération du type d'information à renvoyer
        $type = $this->get("type");


        if(isset($type)){
            // En fonction du type d'information demandé
            switch($type){
                case $this::TYPE_COORD_ADDRESS :
                    // Récupération des coordonées géographiques à traiter
                    $lat = $this->get('lat');
                    $lon = $this->get('lon');

                    if(isset($lat) && isset($lon)){
                        $this->_coordToAddress($lat, $lon);
                    } else {
                        $results = array(
                            "status" => false,
                            "message" => "Les paramètres 'lat' (latitude) et 'lon' (longitude) sont obligatoires."
                        );
                        $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case $this::TYPE_ADDRESS_COORD :
                    // Récupération de l'adresse à traiter
                    $address = $this->get('address');
                    if(isset($address)){
                        $this->_addressToCoord($address);
                    } else {
                        $results = array(
                            "status" => false,
                            "message" => "Le paramètre 'address' (adresse à géolocaliser) est obligatoire."
                        );
                        $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    // Le type n'est pas correct -> Erreur
                    $results = array(
                        "status" => false,
                        "message" => "Le type de requête est incorrect. Les types possibles sont:<br>1 (Coordonnées -> Adresse)<br>2 (Adresse -> Coordonnées)"
                    );
                    $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            // Le type n'est pas fourni -> Erreur
            $results = array(
                "status" => false,
                "message" => "Le paramètre 'type' (type de localisation) est obligatoire."
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function _coordToAddress($lat, $lon){
        if(is_numeric($lat) && is_numeric($lon)){
            $address = $this->geocoding->getAddressFromCoord($lat, $lon);
            $results = array(
                "status" => True,
                "message" => 'Opération réussie',
                "address" => $address
            );
            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                "status" => false,
                "message" => "La latidute et la longitude doivent être des numériques."
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function _addressToCoord($address){
        if(!empty($address)){
            $coord = $this->geocoding->getCoordFromAddress($address);
            $results = array(
                "status" => True,
                "message" => 'Opération réussie',
                "coord" => $coord
            );
            $this->response($results, REST_Controller::HTTP_OK);
        } else{
            $results = array(
                "status" => false,
                "message" => "Le paramètre 'adresse' est obligatoire et ne peut par être vide."
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}