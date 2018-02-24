<?php
/**
 * Created by PhpStorm.
 * User: christopher
 * Date: 18/12/2017
 * Time: 16:03
 */

class Evenement_model extends CI_Model
{

    public function lister($id_groupes, $date_debut, $date_fin, $qte, $detail, $cp, $lat, $lon, $rayon, $styles){

        // Récupération de la ville correspondant au cp passé en paramètres
        $ville = new Ville();

        if(isset($cp)){
            $this->db->select('villes.*');
            $this->db->from('villes');
            $this->db->where('code_postal', $cp);
            $req = $this->db->get();

            if(isset($req)){
                $ville = $req->row(0,'Ville');
            }
        }

        $this->db->distinct();
        $this->db->select('evenements.*');
        $this->db->from('evenements');
        $this->db->where('evenements.est_actif', true);

        $this->db->join('details_evenements', 'details_evenements.id_evenements = evenements.id_evenements');
        $this->db->where('details_evenements.est_actif', true);

        $this->db->join('groupes', 'groupes.id_groupes = evenements.id_groupes');

        // Filtrage sur le groupe
        if(isset($id_groupes)){
            $this->db->where('id_groupes', $id_groupes);
        }

        // Filtrage sur les dates
        if(isset($date_debut) || isset($date_fin)){
            if(isset($date_debut)){
                $this->db->where('details_evenements.date_heure >=', $date_debut);
            }

            if(isset($date_fin)){
                $datetime_fin = new DateTime($date_fin);
                $datetime_fin->setTime(23,59,59);
                $this->db->where('details_evenements.date_heure <=', $datetime_fin->format('Y-m-d H:i:s'));
            }
        }

        // Filtrage sur le rayon
        if(isset($rayon)){
            $this->db->join('adresses', 'adresses.id_adresses = details_evenements.id_adresses');
            $this->db->join('villes', 'villes.id_villes = adresses.id_villes');

            $this->db->group_start();
            // Filtrage sur le code postal
            if(isset($cp)){
                $distance = 'DISTANCE(' . $this->db->escape($ville->lat) . ', ' . $this->db->escape($ville->lon) . ', villes.lat, villes.lon)';

                // Sélection de la distance en plus
                $this->db->select($distance . ' as distance');

                // Groupe de conditions pour pouvoir faire un 'OR' uniquement entre ces conditions là

                $this->db->where('villes.code_postal' ,$cp);
                $this->db->or_where($distance . ' <= ' . $rayon);
            }

            if(isset($lat) && isset($lon)){
                if(isset($cp)){
                    $this->db->where('DISTANCE(villes.lat, villes.lon, '.$lat.', '.$lon.') <=' . $rayon);
                } else {
                    $this->db->or_where('DISTANCE(villes.lat, villes.lon, '.$lat.', '.$lon.') <=' . $rayon);
                }

            }

            $this->db->group_end();
        } else {
            // Filtrage sur le code postal
            if(isset($cp)){
                $this->db->join('adresses', 'adresses.id_adresses = details_evenements.id_adresses');
                $this->db->join('villes', 'villes.id_villes = adresses.id_villes');
                $this->db->where('villes.code_postal' ,$cp);
            }
        }

        // Filtrage sur le style de musique
        if(isset($styles)){

            $this->db->join('styles', 'styles.id_styles = groupes.id_styles');
            $this->db->where('styles.id_styles' ,$styles);
        }

        // Limiter la quantité d'éléments
        if(isset($qte)){
            $this->db->limit($qte);
        }

        //die($this->db->get_compiled_select());
        $query = $this->db->get();

        $events = $query->result('Evenement');
        if($detail){
            foreach($events as $event){
                $this->db->select("details_evenements.*");
                $this->db->from("details_evenements");
                $this->db->where("details_evenements.est_actif", true);
                $this->db->where("id_evenements", $event->id_evenements);

                if(isset($date_debut)){
                    $this->db->where('date_heure >=', $date_debut);
                }

                if(isset($date_fin)){
                    $datetime_fin = new DateTime($date_fin);
                    $datetime_fin->setTime(23,59,59);
                    $this->db->where('date_heure <=', $datetime_fin->format('Y-m-d H:i:s'));
                }

                // Filtrage sur le rayon
                if(isset($rayon)){
                    $this->db->join('adresses', 'adresses.id_adresses = details_evenements.id_adresses');
                    $this->db->join('villes', 'villes.id_villes = adresses.id_villes');
                    $this->db->group_start();
                    // Filtrage sur le code postal
                    if(isset($cp)){
                        $distance = 'DISTANCE(' . $this->db->escape($ville->lat) . ', ' . $this->db->escape($ville->lon) . ', villes.lat, villes.lon)';

                        // Sélection de la distance en plus
                        $this->db->select($distance . ' as distance');

                        // Groupe de conditions pour pouvoir faire un 'OR' uniquement entre ces conditions là

                        $this->db->where('villes.code_postal' ,$cp);
                        $this->db->or_where($distance . ' <= ' . $rayon);
                    }

                    if(isset($lat) && isset($lon)){
                        if(isset($cp)){
                            $this->db->where('DISTANCE(villes.lat, villes.lon, '.$lat.', '.$lon.') <=' . $rayon);
                        } else {
                            $this->db->or_where('DISTANCE(villes.lat, villes.lon, '.$lat.', '.$lon.') <=' . $rayon);
                        }

                    }

                    $this->db->group_end();
                } else {
                    // Filtrage sur le code postal
                    if(isset($cp)){
                        $this->db->join('adresses', 'adresses.id_adresses = details_evenements.id_adresses');
                        $this->db->join('villes', 'villes.id_villes = adresses.id_villes');
                        $this->db->where('villes.code_postal' ,$cp);
                    }
                }

                $query_detail = $this->db->get();
                $event->details = $query_detail->result("EvenementDetail");
            }
        }

        return $events;
    }

    public function ajouter($event, $id_groupes = 0){

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $event->id_groupes = $id_groupes;
        }


        if($this->db->insert('evenements', $event)){
            return $this->db->insert_id();
        } else {
            return 0;
        }
    }

    public function recuperer($id_event, $id_groupes = 0){
        $this->db->from('evenements');
        $this->db->where('id_evenements', $id_event);
        $this->db->where('est_actif', true);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        $query = $this->db->get();

        return $query->row(0, 'Evenement');
    }

    public function recuperer_detail($id_event){
        $this->db->from('details_evenements');
        $this->db->where('id_evenements', $id_event);
        $this->db->where('est_actif', true);

        $query = $this->db->get();

        return $query->result('Detail_Evenement');
    }

    public function modifier($event, $id_evenements, $id_groupes = 0){
        $this->db->where('id_evenements', $id_evenements);

        if(isset($id_groupes) && is_numeric($id_groupes) && $id_groupes > 0){
            $this->db->where('id_groupes', $id_groupes);
        }

        return $this->db->update('evenements', $event);
    }

    public function supprimer($id_evenements){
    // Préparation de la requête
    $this->db->from('evenements');

    // Modification du statut est_actif à false
    $this->db->set('est_actif', false);
    $this->db->where('id_evenements', $id_evenements);

    return $this->db->update();
}

    public function supprimer_detail($id_evenements){
        // Préparation de la requête
        $this->db->from('details_evenements');

        // Modification du statut est_actif à false
        $this->db->set('est_actif', false);
        $this->db->where('id_evenements', $id_evenements);

        return $this->db->update();
    }
}