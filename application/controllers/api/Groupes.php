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
        //$this->load->model('evenement_model', 'evenements');
        $this->load->model('album_model', 'albums');
        $this->load->model('avis_model', 'avis');
        $this->load->model('annonce_model', 'annonces');
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
        $cp = $this->get('cp');
        $rayon = $this->get('rayon');
        $styles = $this->get('styles');

        // Vérifications pour le rayon
        if (isset($rayon) && !isset($cp)){
            // Création et envoi de la réponse
            $results = array(
                'status' => false,
                'message' => 'Le code postal est requis pour filtrer sur le rayon !',
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);

        }

        // Récupération de la liste des groupes correspondants aux critères
        $liste = $this->groupes->lister($cp,$rayon,$styles);

        // Création et envoi de la réponse
        $results = array(
            'status' => true,
            'message' => 'Connexion réussie !',
            'groups' => $liste
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    /**
     * Création d'un groupe
     */
    public function index_post(){
        // Récupération du paramètre nommé 'group' depuis le client ( ex : inscription dans le controleur groupes sur homeband , ligne avec $result = $this->rest->post('groupes', array("group" => $group)); )
        // Traduction de cette ligne : variable $result = rest c'est l'appelle à l'API avec la méthode post , ( 1er paramètres c'est le nom du controlleur à appeller sur API comme par ex : 'groupes/test'
        // ## Exemple de tableau associatif dans homeband/groupes/connexion
        // Et le dernier paramètres est un tableau associatif " => " , "group" c'sst le nom du parametre de l'API on pourrait l'appeller test ,
        $group_post = $this->post('group');
        $group = new Groupe($group_post);
        $group->hash_password();

        // TODO : Vérifier les champs obligatoires

        if($this->groupes->verifie_login($group->login)) {
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
    }

    /**
     * Récupère la fiche d'un groupe
     * @param $id_groupe
     */
    public function detail_get($id_groupe){
        $groupe = $this->groupes->recuperer($id_groupe);

        if(isset($groupe)){
            $results = array(
                'status' => true,
                'message' => 'Operation reussie !',
                'group' => $groupe
            );
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
        $group_put = $this->put('group');
        if (isset($group_put["mot_de_passe"]) && empty($group_put["mot_de_passe"])){
            unset($group_put["mot_de_passe"]);
        } else {

        }

        $group_put = arrayToObject($group_put);
        $group_put->id_groupes=$id_groupe;
        if ($this->groupes->modifier($group_put)){
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
        if ($this->membres->modifier($membres_put)){
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




    // Evenements
    public function evenements_get($id_groupes){
        //TODO date_heure >= date_debut
        //                <= date_fin
    }

    public function evenements_post($id_groupes){
        //TODO Database pas de date debut et fin
    }

    public function evenement_detail_get($id_groupe, $id_evenement){
        //TODO Database pas de date debut et fin
    }

    public function evenement_detail_put($id_groupe, $id_evenement){
        //TODO Database pas de date debut et fin
    }

    public function evenement_detail_delete($id_groupe, $id_evenement){
        //TODO Database pas de date debut et fin
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

        if (isset($album)) {
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'album' => $album
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => true,
                'message' => 'Aucun album correspondant à l\'ID '.$id_albums.' pour ce groupe.',
                'album' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    public function album_detail_put($id_groupe, $id_albums){

    }

    public function album_detail_delete($id_groupe, $id_albums){

    }




    // Avis
    public function avis_get($id_groupe){
    //TODO Database pas de date debut et fin
}

    public function avis_post($id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function avis_detail_get($id_groupe, $id_avis){
        //TODO Database pas de date debut et fin
    }

    public function avis_detail_put($id_groupe, $id_avis){
        //TODO Database pas de date debut et fin
    }

    public function avis_detail_delete($id_groupe, $id_avis){
        //TODO Database pas de date debut et fin
    }




    // Annonces
    public function annonces_get($id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annonces_post($id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annonce_detail_get($id_groupe, $id_annonces){
        //TODO Database pas de date debut et fin
    }

    public function annonce_detail_put($id_groupe, $id_annonces){
        //TODO Database pas de date debut et fin
    }

    public function annonce_detail_delete($id_groupe, $id_annonces){
        //TODO Database pas de date debut et fin
    }




    // Login
    public function login_post(){

        $login = $this->post('login');
        $pass = $this->post('mot_de_passe');

        if(isset($login) && isset($pass)){
            $groupe = new Groupe();
            $groupe->login = $login;
            $groupe->mot_de_passe = $pass;

            $connect = $this->groupes->connecter($groupe);

            if(isset($connect)){

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

}