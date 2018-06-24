<?php

class Adresse extends MY_Object
{
    public $boite = '';
    public $est_actif = true;
    public $id_adresses;
    public $id_villes;
    public $numero;
    public $rue = '';
    public $lat = 0.0;
    public $lon = 0.0;
}