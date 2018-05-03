<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 24-04-18
 * Time: 16:32
 */

class Images extends REST_Controller
{
    public static $_NOT_FOUND_IMAGE = "api/images/no_image.png";

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->helper('url');
    }

    public function index_get($type, $nom){

        $base_folder = "assets/images";

        switch($type){
            case 'group' :
                $folder = "$base_folder/ressources/groups";
                break;
            case 'event':
                $folder = "$base_folder/ressources/events";
                break;
            default:
                $folder = "$base_folder/ressources/others";
                break;

        }

        $img = FCPATH . strtolower("$folder/$nom");
        if(!file_exists($img)){
           header('location: ' . base_url(self::$_NOT_FOUND_IMAGE));
        }

        $filename = basename($img);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));
        switch( $file_extension ) {
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpeg"; break;
            default:
        }

        header("Content-type: $ctype");
        readfile($img);
    }

    public function noimage_get(){
        $img  = FCPATH . "assets/images/ressources/no_image.png";
        header("Content-type: image/png");
        readfile($img);
    }
}