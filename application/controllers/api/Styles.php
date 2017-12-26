<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 26-12-17
 * Time: 22:43
 */

class Styles extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('style_model', 'styles');
    }

    /**
     * Liste des styles
     */
    public function index_get(){

        // Récupération des paramètres
        $qte = $this->get('qte');

        // Récupération de la liste des styles correspondants aux critères
        $liste = $this->styles->lister($qte);

        // Création et envoi de la réponse
        $results = array(
            'status' => true,
            'message' => '',
            'styles' => $liste
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }
}