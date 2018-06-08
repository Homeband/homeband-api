<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 8/06/18
 * Time: 22:37
 */

class Albums
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('album_model', 'album');
        $this->load->model('album_groupe_model', 'album_groupe');
        $this->load->model('groupe_model', 'groupes');
        $this->load->model('membre_model', 'membres');
        $this->load->model('album_model', 'albums');
        $this->load->model('titre_model', 'titres');
    }

    //album
    public function titres_post($id_albums){
        $titre = new Titre($this->post('titre'));
        $titre->id_albums = 0;
        $id = $this->titre->ajouter($titre);

        if($id > 0){
            $titre = $this->titre->recuperer($id);
            $results = array(
                'status' => true,
                'message' => 'Opération réussie !',
                'title' => $titre
            );
            $this->response($results, REST_Controller::HTTP_OK);
        }else{
            $results = array(
                'status' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'album.',
                'title' => null
            );
            $this->response($results, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function titre_put($id_albums){

    }

    public function titre_delete($id_albums){

    }
}