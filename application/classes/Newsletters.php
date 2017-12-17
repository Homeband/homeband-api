<?php

class Newsletter
{
    public $id_news = 0;
    public $sujet = '';
    public $contenu = '';
    public $date_envoi = '';
    public $est_envoyee = false;
    public $est_groupe = false;
    public $est_utilisateur = false;
    public $est_actif = true;
    public $id_administrateurs = 0;
}