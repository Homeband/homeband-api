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
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('membre_model', 'membres');
        $this->load->model('evenement_model', 'evenements');
        $this->load->model('album_model', 'albums');
        $this->load->model('avis_model', 'avis');
        $this->load->model('titre_model', 'titre');
        $this->load->model('annonce_model', 'annonces');
        $this->load->model('utilisateur_model', 'utilisateurs');
        $this->load->library("Geocoding");
    }

    /**
     * Liste des groupes
     */
    public function index_get(){
        // Si on passe dans un tableau associatif :
        /*  $params = array(
                    'cp' => $this->input->get('nomduchamp'),
                    'rayon' => $this->input->get('nomduchamp')
                    'styles' => $this->input->get('nomduchamp')
                );
       */

        // Récupération des paramètres
        $adresse = $this->get('adresse');
        $rayon = $this->get('rayon');
        $styles = $this->get('styles');

        // Vérifications pour le rayon
        /*if (isset($rayon) && (!isset($adresse) || empty($adresse))){
            // Création et envoi de la réponse
            $results = array(
                'status' => false,
                'message' => 'L\'adresse est requise pour filtrer sur le rayon !',
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }*/

        if(isset($rayon) && $rayon == 0)
            $rayon = null;

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

        if ($this->homeband_api->check(0, $id, false)) {

            // Récupération du paramètre nommé 'group' depuis le client ( ex : inscription dans le controleur groupes sur homeband , ligne avec $result = $this->rest->post('groupes', array("group" => $group)); )
            // Traduction de cette ligne : variable $result = rest c'est l'appelle à l'API avec la méthode post , ( 1er paramètres c'est le nom du controlleur à appeller sur API comme par ex : 'groupes/test'
            // ## Exemple de tableau associatif dans homeband/groupes/connexion
            // Et le dernier paramètres est un tableau associatif " => " , "group" c'sst le nom du parametre de l'API on pourrait l'appeller test ,
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

                    $this->response($results, REST_Controller::HTTP_CREATED);
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
        } else {
            $this->response(NULL, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Récupère la fiche d'un groupe
     * @param $id_groupe
     */
    public function detail_get($id_groupe){

        $getMembers = $this->get('membres');

        if(!isset($getMembers) || intval($getMembers) != 1){
            $getMembers = false;
        } else {
            $getMembers = true;
        }

        $groupe = $this->groupes->recuperer($id_groupe);

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
     * @param $id_groupe
     */
    public function detail_put($id_groupe){
        $id_check = 0;
        if($this->homeband_api->check(Homeband_api::$CK_TYPE_GROUPE, $id_check, true) && $id_check == $id_groupe){
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
        } else {
            $results = array(
                'status' => false,
                'message' => 'Accès refusé',
            );
            $this->response($results, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Supprime la fiche d'un groupe
     * @param $id_groupe
     */
    public function detail_delete($id_groupe){
        $this->groupes->supprimer($id_groupe);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Membre
    public function membres_get($id_groupe){
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
        $this->membres->supprimer($id_membres);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }


    public function detail_membre_options($id_groupe,$id_membres){
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Evenements
    public function evenements_get($id_groupe){
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
        $event = new Evenement($this->post('event'));
        $event->id_groupes = $id_groupe;

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

    //TODO
    public function evenement_detail_put($id_groupe, $id_evenements){
        $event = $this->put('event');
        $event = arrayToObject($event);
        if ($this->evenements->modifier($event)){
            $event = $this->evenements->recuperer($id_evenements,$id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'event' => $event
            );
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

    public function album_detail_get($id_groupe, $id_albums)
    {
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
        $this->albums->supprimer($id_albums);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Avis
    public function avis_get($id_groupe){
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
        $this->avis->supprimer($id_avis);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Annonces
    public function annonces_get($id_groupe){
        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');

        $annonces = $this->annonces->lister($id_groupe, $date_debut, $date_fin, $qte);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'annonces' => $annonces
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function annonces_post($id_groupe){
        $annonce = new Annonce($this->post('annonce'));
        $annonce->id_groupes = $id_groupe;

        $id = $this->annonces->ajouter($annonce,$id_groupe);

        if($id > 0){
            $annonce = $this->annonces->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'annonce' => $annonce
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'annonce.',
                'annonce' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function annonce_detail_get($id_groupe, $id_annonces){
        $annonce = $this->annonces->recuperer($id_annonces, $id_groupe);

        if (isset($annonce)) {
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'annonce' => $annonce
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => true,
                'message' => 'Aucun avis correspondant à l\'ID '.$id_annonces.' pour ce groupe.',
                'annonce' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function annonce_detail_put($id_groupe, $id_annonces){
        $annonce = $this->put('annonce');
        $annonce = arrayToObject($annonce);
        if ($this->annonces->modifier($annonce,$id_annonces, $id_groupe)){
            $annonce = $this->annonces->recuperer($id_annonces,$id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'annonce' => $annonce
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }
        else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                'annonce' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function annonce_detail_delete($id_groupe, $id_annonces){
        $this->annonces->supprimer($id_annonces);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }




    // Login
    public function login_post(){
        if($this->homeband_api->check(Homeband_api::$CK_TYPE_GROUPE, false)) {
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
        } else {
            $this->response(NULL, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

}