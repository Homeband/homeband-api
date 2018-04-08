<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 19-12-17
 * Time: 15:59
 */

class Geocoding
{/**
 * CodeIgniter Geocoding Class
 *
 * Call Google API for geocoding.
 */
    protected $_ci;                     // CodeIgniter instance
    protected $api_url = '';            // Google API Url
    protected $api_key = '';            // Application Key for API

    function __construct($api_url = '', $api_key = '')
    {
        $this->_ci = & get_instance();
        $this->_ci->load->library('rest_client');

        if(empty($api_url)){
            $this->api_url = 'https://maps.googleapis.com/maps/api/geocode/';
        } else {
            $this->api_url = $api_url;
        }

        if(empty($api_key) && (($api_key = $this->_ci->config->item('google_api_key')) && empty($api_key))){
            log_message('error', 'The API Key must be specified.');
        } else {
            $this->api_key = $api_key;
        }
    }

    function getCoordFromAddress($address = ''){
        $geocoding = array(
            'lat' => 0.0,
            'lon' => 0.0
        );

        if(!empty($address)) {
            $this->_ci->rest_client->initialize(array('server' => $this->api_url));

            $params = array(
                "address" => $address,
                "key" => $this->api_key
            );

            $api_results = $this->_ci->rest_client->get("json", $params);
            if (isset($api_results) && !empty($api_results) && $api_results->status === "OK") {
                //die(var_dump($api_results));
                $coord = $api_results->results[0]->geometry->location;
                $lat = strval(round($coord->lat, 5));
                $lon = strval(round($coord->lng, 5));

                $geocoding = array(
                    'lat' => $lat,
                    'lon' => $lon
                );
            }
        }

        return $geocoding;
    }

    function getAddressFromCoord($lat = 0.0, $lon = 0.0){
        $address = "";

        $rue = "";
        $numero = "";
        $cp = "";
        $ville = "";

        $this->_ci->rest_client->initialize(array('server' => $this->api_url));
        $latlng = $lat . ',' . $lon;
        $params = array(
            "latlng" => $latlng,
            "key" => $this->api_key
        );

        $api_results = $this->_ci->rest_client->get("json", $params);
        if (isset($api_results) && !empty($api_results) && $api_results->status === "OK") {
            $address_components = $api_results->results[0]->address_components;

            foreach($address_components as $component){
                switch($component->types[0]){
                    case 'street_number' :
                        $tab = explode('-',$component->short_name);
                        $numero = end($tab);
                        break;
                    case 'route' :
                        $rue = $component->short_name;
                        break;
                    case 'locality' :
                        $ville = $component->short_name;
                        break;
                    case 'postal_code' :
                        $cp = $component->short_name;
                        break;
                }
            }

            $address = "$rue $numero, $cp $ville";
        }

        return $address;
    }
}