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
        $id = $this->utilisateur->ajouter($user);

        if($id > 0){
            $annonce = $this->utilisateur->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'user' => $user
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
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
                'message' => 'Aucun avis correspondant à l\'ID '.$id_utilisateurs.' pour ce membres.',
                'user' => null
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }

        }

    public function detail_put($id_utilisateurs){
        $user = $this->put('user');
        $user = arrayToObject($user);
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

    public function detail_delete($id_utilisateurs){
        $this->utilisateur->supprimer($id_utilisateurs);
        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
        );
        $this->response($results, REST_Controller::HTTP_OK);

    }

    //Utilisateur/groupes

    public function U_groupes_get(){
        $cp = $this->get('code_postal');
        $rayon = $this->get('rayon');
        $qte = $this->get('quantite');
        $groups = $this->utilisateur_groupe->lister($cp, $rayon,$qte);

        $results = array(
            'status' => true,
            'message' => 'Operation reussie !',
            'groups' => $groups
        );
        $this->response($results, REST_Controller::HTTP_OK);
    }

    public function U_groupes_post(){
        $groups = new Utilisateur($this->post('group_id'));
        $id = $this->utilisateur_groupe->ajouter($groups);

        if($id > 0){
            $annonce = $this->utilisateur_groupe->recuperer($id);
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
}