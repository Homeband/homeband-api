<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 30/12/17
 * Time: 22:05
 */

class Sessions extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('utilisateur_model', 'utilisateurs');
        //$this->load->model('administrateur_model', 'groupes');
    }

    public function index_post(){

        if(!$this->homeband_api->isAuthorized(array(), array(), false)){
            $this->response(null, REST_Controller::HTTP_UNAUTHORIZED);
        }

        $login = $this->post("login");
        $password = $this->post('mot_de_passe');
        $type = (int)$this->post('type');

        if(!isset($login) || !isset($password) || !isset($type)){
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }

        switch($type){
            case 1:
                $this->_connexion_utilisateur($login, $password);
                //$this->response(, REST_Controller::HTTP_BAD_REQUEST);
                break;
            case 2:
                $this->_connexion_groupe($login, $password);
                break;
            default:
                $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function _connexion_utilisateur($login, $password){
        if (isset($login) && isset($password)) {

            $utilisateur = $this->utilisateurs->connecter($login, $password);
            if (isset($utilisateur)) {

                $results = array(
                    'status' => true,
                    'message' => 'Connexion réussie !',
                    'user' => $utilisateur
                );

                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    'status' => false,
                    'message' => 'Identifiant ou mot de passe incorrect',
                    'user' => NULL
                );

                $this->response($results, REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function _connexion_groupe($login, $password){
        if (isset($login) && isset($password)) {

            $groupe = $this->groupes->connecter($login, $password);

            if (isset($groupe)) {

                $results = array(
                    'status' => true,
                    'message' => 'Connexion réussie !',
                    'group' => $groupe
                );

                $this->response($results, REST_Controller::HTTP_OK);
            } else {
                $results = array(
                    'status' => false,
                    'message' => 'Identifiant ou mot de passe incorrect',
                    'group' => NULL
                );

                $this->response($results, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}