<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 27-12-17
 * Time: 15:59
 */

class Homeband_api
{
    public static $TYPE_USER = 1;
    public static $TYPE_GROUP = 2;

    private $table = "applications_api";

    public function isAuthorized($authorizedTypes = array(), $authorizedID = array(), $identifiedUser = true){
        // Get headers
        $headers = apache_request_headers();
        $ak = (isset($headers['X-Homeband-AK']) && !empty($headers['X-Homeband-AK'])) ? $headers['X-Homeband-AK'] : '';
        $ck = (isset($headers['X-Homeband-CK']) && !empty($headers['X-Homeband-CK'])) ? $headers['X-Homeband-CK'] : '';
        $ts = (isset($headers['X-Homeband-TS']) && !empty($headers['X-Homeband-TS'])) ? $headers['X-Homeband-TS'] : 0;
        $sign = (isset($headers['X-Homeband-SIGN']) && !empty($headers['X-Homeband-SIGN'])) ? $headers['X-Homeband-SIGN'] : '';

        $ck_checked = false;


        // Check TS
        $now = time();
        if($now >= $ts && ($now - $ts) <= 300){
            // Check AK - AS
            if(!empty($ak)) {
                $as = $this->_get_as($ak);
                if (isset($as) && !empty($as)) {

                    // Check CK
                    if(count($authorizedTypes) == 0){
                        // Pas de types autorisés (= Tous)
                        if($identifiedUser){
                            // Un utilisateur doit être identifié
                            $idUser = $this->getID(self::$TYPE_USER);
                            $idGroup = $this->getID(self::$TYPE_GROUP);
                            $ck_checked = $idUser > 0 || $idGroup > 0;
                        } else {
                            // Pas de vérification pour le CK
                            $ck_checked = true;
                        }
                    } else {
                        // On vérifie que le type d'utilisateur est dans le tableau des types autorisés
                        $type = $this->getType();

                        if(in_array($type, $authorizedTypes)){
                            if($identifiedUser){
                                // Un utilisateur doit être identifié
                                if(isset($authorizedID[$type]) && is_array($authorizedID[$type])){
                                    $id = $this->getID($type);

                                    if($id > 0){

                                        if(count($authorizedID[$type]) == 0) {
                                            $ck_checked = true;
                                        } else {
                                            $ck_checked = in_array($id, $authorizedID[$type]);
                                        }
                                    }
                                }
                            } else {
                                // Pas de vérification pour le CK
                                $ck_checked = true;
                            }
                        }
                    }

                    if($ck_checked){

                        //var_dump("OK - CK");
                        $signature = "$1$" . hash("sha256", $as . '+' . $ck . '+' . $ts);
                        //var_dump($signature);
                        return ($signature == $sign);
                    }
                }
            }
        }

        return false;
    }

    public function getType(){
        $headers = apache_request_headers();
        $type = (isset($headers['X-Homeband-TYPE']) && !empty($headers['X-Homeband-TYPE'])) ? $headers['X-Homeband-TYPE'] : "unknown";

        switch($type){
            case 'user':
                return self::$TYPE_USER;
            case 'group':
                return self::$TYPE_GROUP;
            case 'test':
                return 98;
            default:
                return 99;
        }
    }

    public function getID($type){
        // Récupération de la clé client
        $headers = apache_request_headers();
        $ck = (isset($headers['X-Homeband-CK']) && !empty($headers['X-Homeband-CK'])) ? $headers['X-Homeband-CK'] : '';

        // ID à retourner
        $id = 0;

        // Instance CodeIgniter
        $ci = &get_instance();

        // Selon le type d'utilisateur
        switch($type){
            case self::$TYPE_USER: // Utilisateur
                //var_dump()
                $ci->load->model("utilisateur_model", "utilisateurs");
                $user = $ci->utilisateurs->getByCk($ck);
                //var_dump($user);
                if($user != null){
                    $id = $user->id_utilisateurs;
                }
            case self::$TYPE_GROUP: // Groupe
                $ci->load->model("groupe_model", "groupes");
                $group = $ci->groupes->getByCk($ck);

                if($group != null){
                    $id = $group->id_groupes;
                }
                $this->_check_ck_groupes($ck, $id);
            default:
                break;
        }

        return $id;
    }
    /**
     * Récupère la clé secrète associée à la clé d'application
     */
    private function _get_as($ak){
        $ci = &get_instance();
        $ci->db->from($this->table);
        $ci->db->where('application_key', $ak);
        $query = $ci->db->get();

        $row = $query->row(0);
        if(isset($row)){
            if(strtotime($row->validite) >= time()){
                return $row->secret_key;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Vérifie la clé dans de client dans la table groupes
     */
    private function _check_ck_groupes($ck, &$id){
        $ci = &get_instance();
        $ci->db->from('groupes');
        $ci->db->where('api_ck', $ck);
        $query = $ci->db->get();
        $groupe = $query->row(0, 'Groupe');


        if(isset($groupe)){
            $id = $groupe->id_groupes;
            return true;
        }

        return false;
    }

    /**
     * Check the consumer key exist in 'utilisateurs' table
     */
    private function _check_ck_utilisateurs($ck, &$id){
        $ci = &get_instance();
        $ci->db->from('utilisateurs');
        $ci->db->where('api_ck', $ck);

        return ($ci->db->count_all_result() == 1);
    }

    /**
     * Check the consumer key exist in 'administrateurs' table
     */
    private function _check_ck_administrateurs($ck, &$id){
        $ci = &get_instance();
        $ci->db->from('administrateurs');
        $ci->db->where('api_ck', $ck);

        return ($ci->db->count_all_result() == 1);
    }
}