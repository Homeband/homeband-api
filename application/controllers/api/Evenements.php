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
        $this->load->model('adresse_model', 'adresses');
        $this->load->library("Geocoding");
    }

    public function index_get(){
        $id_groupes = $this->get('groupe');
        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');

        // Récupération des paramètres
        $adresse = $this->get('adresse');
        $rayon = $this->get('rayon');
        $styles = $this->get('styles');
        $get_ville=$this->get('get_ville');

        // Vérifications pour le rayon
        if (isset($rayon) && (!isset($adresse) || empty($adresse))){
            // Création et envoi de la réponse
            $results = array(
                'status' => false,
                'message' => 'L\'adresse est requise pour filtrer sur le rayon !',
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }

        if(isset($adresse) && !empty($adresse)){
            $coord = $this->geocoding->getCoordFromAddress($adresse.' Belgium');
            $lat = $coord['lat'];
            $lon = $coord['lon'];
        } else {
            $lat = 0.0;
            $lon = 0.0;
        }

        // Traitement de la requête
        $events = $this->evenements->lister($id_groupes, $date_debut, $date_fin, $qte, $lat, $lon, $rayon, $styles, $get_ville);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'events' => $events
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function detail_get($id_evenement){


        // Traitement de la requête
        $event = $this->evenements->recuperer($id_evenement);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'event' => $event
        );

        if(isset($event)){
            $adresse = $this->adresses->recuperer($event->id_adresses);
            $results["address"] = $adresse;

            $group = $this->groupes->recupererExtraLight($event->id_groupes);
            $results["group"] = $group;
        }


        $this->response($results, REST_Controller::HTTP_OK);
    }


}