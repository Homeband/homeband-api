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

        if($group->inscrire()){
            $results = array(
                'status' => true,
                'group' => $group
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