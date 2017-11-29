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
    }

    public function index_get(){
        $this->response("OK", REST_Controller::HTTP_OK);
    }

    public function index_post(){
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
}