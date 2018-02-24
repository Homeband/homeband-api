<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 24/02/18
 * Time: 12:19
 */

class Evenements extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('membre_model', 'membres');
        $this->load->model('evenement_model', 'evenements');
        $this->load->model('album_model', 'albums');
        $this->load->model('avis_model', 'avis');
        $this->load->model('annonce_model', 'annonces');
        $this->load->model('utilisateur_model', 'utilisateurs');
    }

    public function index_get(){
        $id_groupes = $this->get('groupe');
        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');
        $detail = $this->get('detail');

        // Récupération des paramètres
        $cp = $this->get('cp');
        $rayon = $this->get('rayon');
        $styles = $this->get('styles');
        $lat = $this->get('lat');
        $lon = $this->get('lon');


        // Vérifications pour le rayon
        if (isset($rayon) && (!isset($cp) && (!isset($lat) || !isset($lon)))){
            // Création et envoi de la réponse
            $results = array(
                'status' => false,
                'message' => 'Le code postal ou les coordonnées géographiques (lat/lon) sont requis pour filtrer sur le rayon !',
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);

        }

        // Vérification des paramètres
        if(!isset($detail)){
            $detail = false;
        }

        // Traitement de la requête
        $events = $this->evenements->lister($id_groupes, $date_debut, $date_fin, $qte, $detail, $cp, $lat, $lon, $rayon, $styles);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'events' => $events
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }
}