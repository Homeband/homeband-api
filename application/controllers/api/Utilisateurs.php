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
    }
    //inscription
    public function index_post(){
        $user_post = $this->post('user');
        $user = new Utilisateur_model();
        foreach($user_post as $key => $value){
            $user->$key = $value;
        }

        if($user->inscrire()){
            $results = array(
                'status' => true,
                'user' => $user
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => false
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }

    }
    public function connecter_post(){
        $user_post=$this->post('user');
        $user = new Utilisateur_model();
        foreach($user_post as $key => $value){
            $user->$key = $value;
        }

        if($user->connexion()){
            $results = array(
                'status' => true,
                'user' => $user
            );

            $this->response($results, REST_Controller::HTTP_OK);
        } else {
            $results = array(
                'status' => false
            );

            $this->response($results, REST_Controller::HTTP_OK);
        }
    }
}