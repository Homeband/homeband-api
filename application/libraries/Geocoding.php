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
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author        	Philip Sturgeon
 * @license         http://philsturgeon.co.uk/code/dbad-license
 * @link			http://philsturgeon.co.uk/code/codeigniter-curl
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
            log_message('error', 'cURL Class - PHP was not built with cURL enabled. Rebuild PHP with --with-curl to use cURL.');
        } else {
            $this->api_url = $api_url;
        }

        if(empty($api_key) && (($api_key = $this->_ci->config->item('google_api_key')) && empty($api_key))){

            log_message('error', 'The API Key must be specified.');
        } else {
            $this->api_key = $api_key;
        }
    }

    function get($address = ''){
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
                $coord = $api_results->results[0]->geometry->location;
                $geocoding = array(
                    'lat' => (double)$coord->lat,
                    'lon' => (double)$coord->lng
                );
            }
        }

        return $geocoding;
    }
}