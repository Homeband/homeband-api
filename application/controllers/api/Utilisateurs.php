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
        $this->load->model('Utilisateur_model', 'utilisateur');
        $this->load->model('Utilisateur_groupe_model', 'utilisateur_groupe');
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('membre_model', 'membres');
        $this->load->model('album_model', 'albums');
        $this->load->model('titre_model', 'titres');
    }
    //utilisateur
    public function index_get(){
        $cp = $this->get('code_postal');
        $rayon = $this->get('rayon');

        $users = $this->utilisateur->lister($cp, $rayon);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'users' => $users
        );

        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function index_post(){
        $user = new Utilisateur($this->post('user'));
        $user->hash_password();
        $user->id_utilisateurs = 0;
        $id = $this->utilisateur->ajouter($user);

        if($id > 0){
            $user = $this->utilisateur->recuperer($id);

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

    public function detail_get($id_utilisateurs){
        $user = $this->utilisateur->recuperer($id_utilisateurs);

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

    public function detail_put($id_utilisateurs){
        $initialUser = $this->utilisateur->recuperer($id_utilisateurs);
        if($initialUser != null){
        
            $user = new Utilisateur($this->put('user'));
            if($user->mot_de_passe != $initialUser->mot_de_passe){
                $user->hash_password();    
            }

            $user->id_utilisateurs = $id_utilisateurs;
            
            //$user = arrayToObject($user);
            if ($this->utilisateur->modifier($user,$id_utilisateurs)){
                $user = $this->utilisateur->recuperer($id_utilisateurs);

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

    public function detail_delete($id_utilisateurs){
        $this->utilisateur->supprimer($id_utilisateurs);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    public function forget_password(){
        $email = $this->input->post('email');

        if(isset($email)){
            $user = $this->utilisateur->recupererParEmail($email);
            if($user != null){
                $password = random_string('alnum', 12);
                $user->mot_de_passe = $password;
                $user->hash_password();

                $this->load->library('email');

                $this->email->from('noreply@homeband-heh.be', 'Homeband');
                $this->email->to($email);
                $this->email->subject("Demande d'un nouveau de mot de passe");
                $this->email->message("Votre nouveau mot de passe est: $password");

                if($this->email->send()){
                    $this->utilisateur->modifier($user);
                }
            }
        } else {

        }

    }

    //Utilisateur/groupes

    public function U_groupes_get($id_utilisateurs){
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
        $this->utilisateur_groupe->supprimer($id_utilisateurs,$id_groupes);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    //Utilisateur/avis

    public function U_avis_get($id_utilisateurs){
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
        $this->utilisateur_groupe->supprimer($id_utilisateurs,$id_groupes);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );

        $this->response($results, REST_Controller::HTTP_OK);

    }

    //Ajout de la liaison addFavourite groupe 
    public function declare_connexion_groupe_post($id_utilisateur,$id_groupe){

        $get_groupe = $this->post("get_groupe");
        $get_membres = $this->post("get_membres");
        $get_albums = $this->post("get_albums");
        $get_titres = $this->post("get_titres");


        if( $this->utilisateur_groupe->recuperer($id_utilisateur,$id_groupe) == null){
            $this->utilisateur_groupe->ajouter($id_utilisateur,$id_groupe);
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
        } else {
           $results = array(
               'status' => false,
               'message' => 'Erreur la liaison existe déjà !',
           );

           $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
       }
    }

    //Supprimer la liaison entre le groupe et l'utilisateur
    public function remove_connexion_groupe_delete($id_utilisateur,$id_groupe){

        if( $this->utilisateur_groupe->recuperer($id_utilisateur,$id_groupe) != null){
            $this->utilisateur_groupe->supprimer($id_utilisateur,$id_groupe);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
            );

            $this->response($results, REST_Controller::HTTP_OK);

        } else {
            $results = array(
                'status' => false,
                'message' => 'Erreur lors de la suppression !',
            );
 
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
