<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 27-12-17
 * Time: 15:59
 */

class Homeband_api
{
    public static $CK_TYPE_GROUPE = 1;
    public static $CK_TYPE_UTILISATEUR = 2;
    public static $CK_TYPE_ADMINISTRATEUR = 3;
    public static $CK_TYPE_ALL = 4;

    private $table = "applications_api";

    /**
     * Sign the requests to Homeband API
     */
    public function check($ck_type = 1, &$id = NULL, $ck_force = true){

        $ts_checked = false;
        $as_checked = false;
        $consumer_checked = false;

        // Get headers
        $headers = apache_request_headers();
        $ak = (isset($headers['X-Homeband-AK']) && !empty($headers['X-Homeband-AK'])) ? $headers['X-Homeband-AK'] : '';
        $ck = (isset($headers['X-Homeband-CK']) && !empty($headers['X-Homeband-AK'])) ? $headers['X-Homeband-CK'] : '';
        $ts = (isset($headers['X-Homeband-TS']) && !empty($headers['X-Homeband-AK'])) ? $headers['X-Homeband-TS'] : 0;
        $sign = (isset($headers['X-Homeband-SIGN']) && !empty($headers['X-Homeband-SIGN'])) ? $headers['X-Homeband-SIGN'] : '';

        // Check AK - AS
        if(!empty($ak)) {
            $as = $this->_get_as($ak);
            if (isset($as) && !empty($as)) {
                $as_checked = true;
            }
        }

        // Check TS
        $now = time();
        if($now >= $ts && ($now - $ts) <= 300){
            $ts_checked = true;
        }

        // Check CK
        if($ck_force){
            if(!empty($ck)){
                switch($ck_type){
                    case 1 :
                        $consumer_checked = $this->_check_ck_groupes($ck, $id);
                        break;

                    case 2 :
                        $consumer_checked = $this->_check_ck_utilisateurs($ck, $id);
                        break;

                    case 3 :
                        $consumer_checked = $this->_check_ck_administrateurs($ck, $id);
                        break;

                    case 4 :
                        $consumer_checked = ($this->_check_ck_groupes($ck, $id) || $this->_check_ck_utilisateurs($ck, $id) || $this->_check_ck_administrateurs($ck, $id));
                        break;
                }
            }
        } else {
            $consumer_checked = true;
        }


        // Check signature
        if($ts_checked && $consumer_checked && $as_checked){
            $signature = "$1$" . hash("sha256", $as . '+' . $ck . '+' . $ts);
            return ($signature == $sign);
        } else {
            return false;
        }
    }

    /**
     * Get the secret key in relation with application key
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
     * Check the consumer key exist in 'groupes' table
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