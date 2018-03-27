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

    public function index_get(){
        $type = $this->get("type");

        if(isset($type)){
            switch($type){
                case TYPE_COORD_ADDRESS :
                    $lat = $this->get('lat');
                    $lon = $this->get('lon');

                    if(isset($lat) && isset($lon)){
                        $this->_coordToAddress();
                    } else {
                        $results = array(
                            "status" => false,
                            "message" => "Les paramètres 'lat' (latitude) et 'lon' (longitude) sont obligatoires."
                        );
                        $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case TYPE_ADDRESS_COORD :
                    $address = $this->get('address');
                    if(isset($address)){
                        $this->_addressToCoord();
                    } else {
                        $results = array(
                            "status" => false,
                            "message" => "Le paramètre 'address' (adresse à géolocaliser) est obligatoire."
                        );
                        $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    $results = array(
                        "status" => false,
                        "message" => "Le type de requête est incorrect. Les types possibles sont:<br>1 (Coordonnées -> Adresse)<br>2 (Adresse -> Coordonnées)"
                    );
                    $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $results = array(
                "status" => false,
                "message" => "Le paramètre 'type' (type de localisation) est obligatoire."
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function _coordToAddress($lat, $lon){

    }

    private function _addressToCoord($address){

    }
}