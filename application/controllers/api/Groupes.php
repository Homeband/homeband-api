<?php

/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 27/11/17
 * Time: 19:16
 */
class Groupes extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('adresse_model', 'adresses');
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('membre_model', 'membres');
        $this->load->model('evenement_model', 'evenements');
        $this->load->model('album_model', 'albums');
        $this->load->model('avis_model', 'avis');
        $this->load->model('titre_model', 'titre');
        $this->load->model('annonce_model', 'annonces');
        $this->load->model('utilisateur_model', 'utilisateurs');
        $this->load->library("Homeband_api");
        $this->load->library("Geocoding");
    }

    /**
     * Liste des groupes
     */
    public function index_get(){

        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array()
        );
        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Récupération des paramètres
        $adresse = $this->get('adresse');
        $rayon = $this->get('rayon');
        $styles = $this->get('styles');

        // Vérification du rayon
        if(isset($rayon) && $rayon == 0){
            $rayon = null;
        }

        // Vérification de l'adresse
        if(isset($adresse) && !empty($adresse)){
            $coord = $this->geocoding->getCoordFromAddress($adresse.' Belgium');
            $lat = $coord['lat'];
            $lon = $coord['lon'];
        } else {
            $lat = null;
            $lon = null;
        }

        // Récupération de la liste des groupes correspondants aux critères
        $liste = $this->groupes->lister($lat, $lon, $rayon, $styles);

        // Création et envoi de la réponse
        $results = array(
            'status' => true,
            'message' => 'Opération réussie !',
            'groups' => $liste
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    /**
     * Création d'un groupe
     */
    public function index_post()
    {
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array();

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID, false)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Récupération des paramètres
        $group_post = $this->post('group');
        $group = new Groupe($group_post);
        $group->hash_password();
        $group->id_groupes = 0;
        $group->api_ck = "";

        // TODO : Vérifier les champs obligatoires

        if ($this->groupes->verifie_login($group->login)) {
            if ($this->groupes->inscrire($group)) {
                $results = array(
                    'status' => true,
                    'message' => 'Inscription réussie !'
                );

                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    'status' => false,
                    'message' => 'Erreur lors de l\'inscription'
                );

                $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $results = array(
                'status' => false,
                'message' => 'Le login n\'est pas disponible.'
            );

            $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Récupère la fiche d'un groupe
     * @param $id_groupe Identifiant du groupe
     */
    public function detail_get($id_groupe){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $getMembers = $this->get('membres');

        if(!isset($getMembers) || intval($getMembers) != 1){
            $getMembers = false;
        } else {
            $getMembers = true;
        }

        $typeRequest = $this->homeband_api->getType();
        $groupe = $this->groupes->recuperer($id_groupe);
        unset($groupe->mot_de_passe);
        if($typeRequest != Homeband_api::$TYPE_GROUP){
            unset($groupe->api_ck);
        }

        if(isset($groupe)){

            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'group' => $groupe,
            );


            if($getMembers){
                $membres = $this->membres->lister(null, null, null, $id_groupe);
                $results['members'] = $membres;
            }

            $this->response($results, REST_Controller::HTTP_OK);
        } else {

        }$results = array(
            'status' => false,
            'message' => 'Aucun groupe correspondant à l\'id '.$id_groupe,
            'group' => null
        );

        $this->response($results, REST_Controller::HTTP_NOT_FOUND);
    }

    /**
     * Modifie la fiche d'un groupe
     * @param $id_groupe Identifiant du groupe
     */
    public function detail_put($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $groupe = new Groupe($this->put('group'));
        if(!empty($groupe->mot_de_passe)){
            $groupe->hash_password();
        }

        if ($this->groupes->modifier($groupe)){
            $groupe = $this->groupes->recuperer($id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'group' => $groupe
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }
        else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                'group' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    /**
     * Supprime la fiche d'un groupe
     * @param $id_groupe Identifiant du groupe
     */
    public function detail_delete($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $this->groupes->supprimer($id_groupe);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Membre
    public function membres_get($id_groupe){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');

        $members = $this->membres->lister($date_debut, $date_fin,$qte,$id_groupe);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'members' => $members
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function membres_post($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $member = $this->post('member');
        $member = arrayToObject($member);
        $member->id_groupes=$id_groupe;
        $id= $this->membres->ajouter($member);
        if($id>0){
            $member = $this->membres->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'member' => $member
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création du membre !',
                'member' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function detail_membre_get($id_groupe,$id_membres){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $member = $this->membres->recuperer($id_membres,$id_groupe);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'member' => $member
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function detail_membre_put($id_groupe,$id_membres)
    {
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $membres_put = $this->put('member');
        $membres_put = arrayToObject($membres_put);
        if ($this->albums->modifier($membres_put)){
            $member = $this->membres->recuperer($id_membres,$id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'member' => $member
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }
        else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                'member' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function detail_membre_delete($id_groupe,$id_membres){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        if($this->membres->recuperer($id_membres, $id_groupe) != null) {
            $this->membres->supprimer($id_membres);
        }

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }

   // Pour les requêtes AJAX
    public function detail_membre_options(){
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Evenements
    public function evenements_get($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        try{
            $date_debut = $this->get('date_debut');
            $date_fin = $this->get('date_fin');
            $qte = $this->get('qte');


            // Traitement de la requête
            $events = $this->evenements->lister($id_groupe, $date_debut, $date_fin, $qte, null, null, null, null, true);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'events' => $events
            );
            $this->response($results, REST_Controller::HTTP_OK);
        } catch (Exception $ex) {
            $results = array(
                'status' => false,
                'message' => $ex->getMessage(),
                'events' => null
            );
            $this->response($results, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function evenements_post($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Adresse
        $address = new Adresse($this->post('address'));
        $address->id_adresses = 0;


        // Evenements
        $event = new Evenement($this->post('event'));
        $event->id_groupes = $id_groupe;
        $event->id_adresses = $this->adresses->ajouter($address);

        $id = $this->evenements->ajouter($event);

        if($id > 0){
            $event = $this->evenements->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'event' => $event
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'evenement.',
                'event' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function evenement_detail_get($id_groupe, $id_evenements){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $detail = $this->get('detail');
        $event = $this->evenements->recuperer($id_evenements, $id_groupe);
        $evenement_detail = array();
        if (isset($event)) {

            if (isset($detail) && ($detail==1)){
                $evenement_detail=$this->evenements->recuperer_detail($id_evenements);
            }

            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'event' => $event,
                'detail' => $evenement_detail
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => true,
                'message' => 'Aucun album correspondant à l\'ID '.$id_evenements.' pour ce groupe.',
                'event' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function evenement_detail_put($id_groupe, $id_evenements){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $event = $this->put('event');
        $event = arrayToObject($event);
        if ($this->evenements->modifier($event, $id_evenements, $id_groupe)){
            $event = $this->evenements->recuperer($id_evenements,$id_groupe);

            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'event' => $event,
            );

            $address = $this->post("address");
            if(isset($address)){
                $obj = new Adresse($address);
                if($this->adresses->modifier($obj)){
                    $obj = $this->adresses->recuperer($obj->id_adresses);
                    $results["address"] = $obj;
                }
            }

            $this->response($results, REST_Controller::HTTP_OK);
        }
        else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                'event' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function evenement_detail_delete($id_groupe, $id_evenements){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->evenements->supprimer($id_evenements);
        $this->evenements->supprimer_detail($id_evenements);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Albums
    public function albums_get($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');

        $albums = $this->albums->lister($id_groupe, $date_debut, $date_fin, $qte);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'albums' => $albums
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }
    
    public function albums_post($id_groupe){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $album = new Album($this->post('album'));
        $album->id_groupes = $id_groupe;
        $album->id_albums = 0;

        $id = $this->albums->ajouter($album);

        if($id > 0){
            $album = $this->albums->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'album' => $album
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'album.',
                'album' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function album_detail_get($id_groupe, $id_albums) {
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $album = $this->albums->recuperer($id_albums, $id_groupe);
        $titre = $this->titre->lister($id_albums,$id_groupe);

        if (isset($album)) {
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'album' => $album,
                'titres' => $titre
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => true,
                'message' => 'Aucun album correspondant à l\'ID '.$id_albums.' pour ce groupe.',
                'album' => null,
                'titres' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function album_detail_put($id_groupe, $id_albums){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $album_param = $this->put('album');
        $album = new Album($album_param);

        if ($this->albums->modifier($album, $id_albums, $id_groupe)){

            $album_new = $this->albums->recuperer($id_albums,$id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'album' => $album_new
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }
        else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                'album' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function album_detail_delete($id_groupe, $id_albums){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->albums->supprimer($id_albums);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Avis
    public function avis_get($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');
        $type = $this->get('type');

        $comments = $this->avis->lister($id_groupe,0, $date_debut, $date_fin, $qte, $type);
        foreach($comments as $comment){
            $user = $this->utilisateurs->recuperer($comment->id_utilisateurs);
            $comment->username = isset($user) ? $user->login : "Inconnu";
        }

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'comments' => $comments
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function avis_post($id_groupe){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array()
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }


        $comment = new Avis($this->post('comment'));
        $comment->id_groupes = $id_groupe;

        $id = $this->avis->ajouter($comment);

        if($id > 0){
            $comment = $this->avis->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'comment' => $comment
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'album.',
                'comment' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function avis_detail_get($id_groupe, $id_avis){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER, Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $comment = $this->avis->recuperer($id_avis, $id_groupe);

        if (isset($comment)) {
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'comment' => $comment
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => true,
                'message' => 'Aucun avis correspondant à l\'ID '.$id_avis.' pour ce groupe.',
                'comment' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function avis_detail_put($id_groupe, $id_avis){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $comment = $this->put('comment');
        $comment = arrayToObject($comment);
        if ($this->avis->modifier($comment, $id_avis, $id_groupe)){
            $comment = $this->avis->recuperer($id_avis,$id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'comment' => $comment
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }
        else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                'comment' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function avis_status_put($id_groupe, $id_avis){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $status = $this->put('status');
        if(isset($status)){
            $avis = $this->avis->recuperer($id_avis, $id_groupe);
            if($avis != null){
                if(boolval($status)){
                    $avis->est_verifie = true;
                    $avis->est_accepte = true;
                } else {
                    $avis->est_verifie = true;
                    $avis->est_accepte = false;
                }

                if($this->avis->modifier($avis, $id_avis, $id_groupe)){
                    $result = array(
                        'status' => true,
                        'message' => "Opération réussie !",
                        'comment' => $this->avis->recuperer($id_avis, $id_groupe)
                    );

                    $this->response($result, self::HTTP_OK);
                } else {
                    $result = array(
                        'status' => false,
                        'message' => "Erreur lors de la modification !",
                        'comment' => $this->avis->recuperer($id_avis, $id_groupe)
                    );

                    $this->response($result, self::HTTP_OK);
                }
            } else {
                $result = array(
                    'status' => false,
                    'message' => "L'avis n'a pas été trouvé dans la base de données"
                );

                $this->response($result, self::HTTP_OK);
            }
        } else {
            $result = array(
                'status' => false,
                'message' => "Le statut de l'avis doit être passé en paramètre."
            );

            $this->response($result, self::HTTP_OK);
        }

    }

    public function avis_detail_delete($id_groupe, $id_avis){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array($id_groupe)
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->avis->supprimer($id_avis);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }


    // Login
    public function login_post(){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array()
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID, false)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $login = $this->post('login');
        $pass = $this->post('mot_de_passe');

        if (isset($login) && isset($pass)) {
            $groupe = new Groupe();
            $groupe->login = $login;
            $groupe->mot_de_passe = $pass;

            $connect = $this->groupes->connecter($groupe);

            if (isset($connect)) {

                $results = array(
                    'status' => true,
                    'message' => 'Connexion réussie !',
                    'group' => $connect
                );

                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    'status' => false,
                    'message' => 'Identifiant ou mot de passe incorrect',
                    'group' => null
                );

                $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Demande d'un nouveau mot de passe
     */
    public function forget_password_post(){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_GROUP);
        $authorizedID = array(
            Homeband_api::$TYPE_GROUP => array(),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID,false)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }
        // Paramètres d'appel
        $email = $this->input->post('email');

        if(isset($email)){
            // transformation de l'email en minuscules
            $email = strtolower($email);

            // Recherche d'un utilisateur correspondant
            $group = $this->groupes->recuperer_par_email($email);
            if($group != null){

                // Génération d'un nouveau mot de passe
                $password = random_string('alnum', 12);
                $group->mot_de_passe = $password;
                $group->hash_password();

                // Création de l'email pour informer l'utilisateur
                $this->load->library('email');

                $this->email->from('no-reply@homeband-heh.be', 'Homeband');
                $this->email->to($email);
                $this->email->subject("Demande d'un nouveau de mot de passe");
                $this->email->message("Votre nouveau mot de passe est: $password");

                if($this->email->send()){
                    // Si l'envoi d'email réussi, on met à jour le mot de passe dans la base de données
                    $this->groupes->modifier($group, $group->id_groupes);

                    // Retour
                    $result = array(
                        "status" => true,
                        "message" => "Opération réussie !"
                    );
                } else {
                    // Retour
                    $result = array(
                        "status" => false,
                        "message" => "Une erreur interne s'est produite, veuillez réessayer plus tard."
                    );
                }

                $this->response($result, REST_Controller::HTTP_OK);
            } else {
                $result = array(
                    "status" => false,
                    "message" => "Aucun utilisateur ne correspond à l'adresse email renseignée."
                );

                $this->response($result, REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $result = array(
                "status" => false,
                "message" => "L'adresse email de l'utilisateur est obligatoire."
            );

            $this->response($result, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}