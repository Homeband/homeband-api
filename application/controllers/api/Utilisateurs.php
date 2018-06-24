<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 29-11-17
 * Time: 11:26
 */

class Utilisateurs extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('utilisateur_model', 'utilisateur');
        $this->load->model('utilisateur_groupe_model', 'utilisateur_groupe');
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('membre_model', 'membres');
        $this->load->model('album_model', 'albums');
        $this->load->model('titre_model', 'titres');
    }

    /**
     * Liste des utilisateurs correspondants à des critères spécifiques
     * -- Fonction réservée à l'administration --
     */
    public function index_get(){
        // TODO : Signature API

        // Paramètres
        $cp = $this->get('code_postal');
        $rayon = $this->get('rayon');

        // Recherche des utilisateurs correspondants
        $users = $this->utilisateurs->lister($cp, $rayon);

        // Retour du résultat
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'users' => $users
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }

    /**
     * Inscription d'un utilisateur
     */
    public function index_post(){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID,false)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Création d'un objet utilisateur avec les données envoyées
        $user = new Utilisateur($this->post('user'));

        // Hachage du mot de passe
        $user->hash_password();

        // Remise à 0 de lidentifiant unique (géré par la base de données)
        $user->id_utilisateurs = 0;

        // Vérification de la disponibilité du login
        if(!$this->utilisateurs->verifie_login($user->login)){
            $results = array(
                'status' => false,
                'message' => 'Le login n\'est pas disponible.'
            );

            $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Vérification de la disponibilité de l'email
        if(!$this->utilisateurs->verifie_email($user->email)){
            $results = array(
                'status' => false,
                'message' => 'L\'adresse email est déjà utilisée.'
            );

            $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Création de l'utilisateur
        $id = $this->utilisateurs->ajouter($user);

        // Retour du résultat
        if($id > 0){
            // Récupération de l'utilisateur
            $user = $this->utilisateurs->recuperer($id);

            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'user' => $user
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'annonce.',
                'user' => null
            );

            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Fiche d'un utilisateur
     * @param $id_utilisateurs Identifiant de l'utilisateur
     */
    public function detail_get($id_utilisateurs){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $user = $this->utilisateurs->recuperer($id_utilisateurs);

        if (isset($user)) {
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'user' => $user
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => true,
                'message' => 'Aucun utilisateur correspondant à l\'ID '.$id_utilisateurs,
                'user' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }

    /**
     * Modification d'un utilisateur
     * @param $id_utilisateurs Identifiant de l'utilisateur
     */
    public function detail_put($id_utilisateurs){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }
        // TODO : Commentaires
        // TODO : Nomenclature
        $initialUser = $this->utilisateurs->recuperer($id_utilisateurs);
        if($initialUser != null){
            $user = new Utilisateur($this->put('user'));
            if($user->mot_de_passe != "" && $user->mot_de_passe != $initialUser->mot_de_passe){
                $user->hash_password();    
            }

            $user->id_utilisateurs = $id_utilisateurs;

            if ($this->utilisateurs->modifier($user,$id_utilisateurs)){
                $user = $this->utilisateurs->recuperer($id_utilisateurs);

                $results = array(
                    'status' => true,
                    'message' => 'Operation reussie !',
                    'user' => $user
                );

                $this->response($results, REST_Controller::HTTP_OK);
            }
            else{
                $results = array(
                    'status' => false,
                    'message' => 'Une erreur est survenue lors de la modification des données. Veuillez vérifier les données envoyées !',
                    'user' => null
                );

                $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
            }
        }

    }

    /**
     * Demande d'un nouveau mot de passe
     */
    public function forget_password_post(){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array(),
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
            $user = $this->utilisateurs->recuperer_par_email($email);
            if($user != null){

                // Génération d'un nouveau mot de passe
                $password = random_string('alnum', 12);
                $user->mot_de_passe = $password;
                $user->hash_password();

                // Création de l'email pour informer l'utilisateur
                $this->load->library('email');

                $this->email->from('no-reply@homeband-heh.be', 'Homeband');
                $this->email->to($email);
                $this->email->subject("Demande d'un nouveau de mot de passe");
                $this->email->message("Votre nouveau mot de passe est: $password");

                if($this->email->send()){
                    // Si l'envoi d'email réussi, on met à jour le mot de passe dans la base de données
                    $this->utilisateurs->modifier($user, $user->id_utilisateurs);

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

    //Utilisateur/groupes

    public function U_groupes_get($id_utilisateurs){
        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $cp = $this->get('code_postal');
        $rayon = $this->get('rayon');
        $qte = $this->get('quantite');
        $groups = $this->utilisateur_groupe->lister($id_utilisateurs,$cp, $rayon,$qte);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'groups' => $groups
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function U_groupes_post($id_utilisateurs){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $id_groupes = $this->post('group_id');
       if ($this->utilisateur_groupe->ajouter($id_utilisateurs,$id_groupes))
       {
            $groups = $this->utilisateur_groupe->recuperer($id_utilisateurs,$id_groupes);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'groups' => $groups
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\' ajout du groupe en favoris.',
                'groups' => null
            );

            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    public function U_groupes_delete($id_utilisateurs,$id_groupes){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->utilisateur_groupe->supprimer($id_utilisateurs,$id_groupes);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    //Utilisateur/avis

    public function U_avis_get($id_utilisateurs){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $date_debut = $this->get('date_debut');
        $date_fin = $this->get('date_fin');
        $qte = $this->get('qte');
        $avis = $this->avis->lister(0,$id_utilisateurs, $date_debut, $date_fin, $qte);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'annonces' => $avis
        );

        $this->response($results, REST_Controller::HTTP_OK);
}

    public function U_avis_post($id_utilisateurs){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $id_groupes = $this->post('group_id');
        if ($this->utilisateur_groupe->ajouter($id_utilisateurs,$id_groupes))
        {
            $groups = $this->utilisateur_groupe->recuperer($id_utilisateurs,$id_groupes);

            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'groups' => $groups
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\' ajout du groupe en favoris.',
                'groups' => null
            );

            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    public function U_avis_delete($id_utilisateurs,$id_groupes){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateurs),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->utilisateur_groupe->supprimer($id_utilisateurs,$id_groupes);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    //Ajout de la liaison addFavourite groupe 
    public function declare_connexion_groupe_post($id_utilisateur,$id_groupe){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateur),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $get_groupe = $this->post("get_groupe");
        $get_membres = $this->post("get_membres");
        $get_albums = $this->post("get_albums");
        $get_titres = $this->post("get_titres");


        if( $this->utilisateur_groupe->recuperer($id_utilisateur,$id_groupe) == null) {
            $this->utilisateur_groupe->ajouter($id_utilisateur, $id_groupe);
        }
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',


            );

            if(isset($get_groupe) && intval($get_groupe) == 1){
                $groupe = $this->groupes->recuperer($id_groupe);
                $results["group"] = $groupe;
            }

            if(isset($get_membres) && intval($get_membres) == 1){
                $membres = $this->membres->lister(null, null, null, $id_groupe);
                $results["members"] = $membres;
            }

            if(isset($get_albums) && intval($get_albums) == 1){
                $albums = $this->albums->lister($id_groupe);
                $results["albums"] = $albums;
            }

            if(isset($get_titres) && intval($get_titres) == 1){
                $titres = $this->albums->lister(null,$id_groupe);
                $results["titles"] = $titres;
            }

            $this->response($results, REST_Controller::HTTP_OK);
    }

    //Supprimer la liaison entre le groupe et l'utilisateur
    public function remove_connexion_groupe_delete($id_utilisateur,$id_groupe){

        // Vérification de l'autorisation
        $authorizedTypes = array(Homeband_api::$TYPE_USER);
        $authorizedID = array(
            Homeband_api::$TYPE_USER => array($id_utilisateur),
        );

        if(!$this->homeband_api->isAuthorized($authorizedTypes, $authorizedID)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        if( $this->utilisateur_groupe->recuperer($id_utilisateur,$id_groupe) != null){
            $this->utilisateur_groupe->supprimer($id_utilisateur,$id_groupe);
        }

        $results = array(
            'status' => true,
            'message' => 'Opération réussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }
}
