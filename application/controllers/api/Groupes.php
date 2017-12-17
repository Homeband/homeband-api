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
        $this->load->model('groupe_model', 'groupe');
        $this->load->model('membres_model', 'membres');
    }

    public function index_get(){
        // Si on passe dans un tableau associatif :
        /*  $params = array(
                    'cp' => $this->input->get('nomduchamp'),
                    'rayon' => $this->input->get('nomduchamp')
                    'styles' => $this->input->get('nomduchamp')
                );
       */
        $cp = $this->get('cp');
        $rayon = $this->get('rayon');
        $styles = $this->get('styles');

        if (isset($rayon) && !isset($cp)){
            $results = array(
                'status' => false,
                'message' => 'Le code postal est requis pour filtrer sur le rayon !',
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);

        }
       $liste = $this->groupe->lister($cp,$rayon,$styles);
        $results = array(
            'status' => true,
            'message' => 'Connexion réussie !',
            'groups' => $liste
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    public function detail_get($id_groupe){
        $groupe = $this->groupe->recuperer($id_groupe);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'group' => $groupe
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }
    public function detail_put($id_groupe){
        $group_put = $this->put('group');
        if (isset($group_put["mot_de_passe"]) && empty($group_put["mot_de_passe"])){
            unset($group_put["mot_de_passe"]);
        }
        else{}

       $group_put = arrayToObject($group_put);
        $group_put->id_groupes=$id_groupe;
        if ($this->groupe->modifier($group_put)){
            $groupe = $this->groupe->recuperer($id_groupe);
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

    public function detail_delete($id_groupe){
        $this->groupe->supprimer($id_groupe);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function index_post(){
        // Récupération du paramètre nommé 'group' depuis le client ( ex : inscription dans le controleur groupes sur homeband , ligne avec $result = $this->rest->post('groupes', array("group" => $group)); )
        // Traduction de cette ligne : variable $result = rest c'est l'appelle à l'API avec la méthode post , ( 1er paramètres c'est le nom du controlleur à appeller sur API comme par ex : 'groupes/test'
        // ## Exemple de tableau associatif dans homeband/groupes/connexion
        // Et le dernier paramètres est un tableau associatif " => " , "group" c'sst le nom du parametre de l'API on pourrait l'appeller test ,
        $group_post = $this->post('group');

        $group = new Groupe_model();
        foreach($group_post as $key => $value){
            $group->$key = $value;
        }

        // TODO : Vérifier les champs obligatoires

        if($group->inscrire()){
            $results = array(
                'status' => true,
                'message' => 'Inscription réussie !',
                'group' => $group
            );

            $this->response($results, REST_Controller::HTTP_CREATED);
        } else {
            $results = array(
                'status' => false,
                'message' => 'Erreur lors de l\'inscription'
            );

            $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function login_post(){
        $login = $this->post('login');
        $pass = $this->post('mot_de_passe');

        if(isset($login) && isset($pass)){
            $group = new Groupe_model();
            $group->login = $login;
            $group->mot_de_passe = $pass;

            if($group->connecter()){
                $results = array(
                    'status' => true,
                    'message' => 'Connexion réussie !',
                    'group' => $group
                );

                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    'status' => false,
                    'message' => 'Identifiant ou mot de passe incorrect'
                );

                $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

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

    public function evenements_get($date_debut, $date_fin,$qte,$id_groupe){
        //TODO date_heure >= date_debut
        //                <= date_fin
    }

    public function evenements_post($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }


    public function evenementsDetails_get($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function evenementsDetails_put($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function evenementsDetails_delete($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function albums_get($date_debut, $date_fin,$qte,$id_groupe){
    //TODO Database pas de date debut et fin
}

    public function albums_post($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function albumsDetails_get($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function albumsDetails_put($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function albumsDetails_delete($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function avis_get($date_debut, $date_fin,$qte,$id_groupe){
    //TODO Database pas de date debut et fin
}

    public function avis_post($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function avisDetails_get($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function avisDetails_put($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function avisDetails_delete($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annonces_get($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annonces_post($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annoncesDetails_get($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annoncesDetails_put($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }

    public function annoncesDetails_delete($date_debut, $date_fin,$qte,$id_groupe){
        //TODO Database pas de date debut et fin
    }


}