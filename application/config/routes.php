<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
| https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/
//$route['api/example/users/(:num)'] = 'api/example/users/id/$1'; // Example 4
//$route['api/example/users/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'api/example/users/id/$1/format/$3$4'; // Example 8

// Villes
$route['api/villes'] = 'api/villes';
$route['api/villes/(:num)'] = 'api/villes/detail/$1';
$route['api/villes/geocoding'] = 'api/villes/geo';

// Connexion
$route['api/sessions']['POST'] = 'api/sessions';


// Groupes
$route['api/groupes'] = 'api/groupes';
$route['api/groupes/(:num)'] = 'api/groupes/detail/$1';

$route['api/groupes/(:num)/membres'] = 'api/groupes/membres/$1';
$route['api/groupes/(:num)/membres/(:num)'] = 'api/groupes/detail_membre/$1/$2';

$route['api/groupes/(:num)/evenements'] = 'api/groupes/evenements/$1';
$route['api/groupes/(:num)/evenements/(:num)'] = 'api/groupes/evenement_detail/$1/$2';

$route['api/groupes/(:num)/albums'] = 'api/groupes/albums/$1';
$route['api/groupes/(:num)/albums/(:num)'] = 'api/groupes/album_detail/$1/$2';

$route['api/groupes/(:num)/avis'] = 'api/groupes/avis/$1';
$route['api/groupes/(:num)/avis/(:num)'] = 'api/groupes/avis_detail/$1/$2';

$route['api/groupes/(:num)/annonces'] = 'api/groupes/annonces/$1';
$route['api/groupes/(:num)/annonces/(:num)'] = 'api/groupes/annonce_detail/$1/$2';


//Utilisateurs

$route['api/utilisateurs/forget'] = 'api/utilisateurs/forget_password';

$route['api/utilisateurs'] = 'api/utilisateurs';
$route['api/utilisateurs/(:num)'] = 'api/utilisateurs/detail/$1';

$route['api/utilisateurs/(:num)/groupes'] = 'api/utilisateurs/U_groupes/$1';
$route['api/utilisateurs/(:num)/groupes/(:num)']["POST"] = 'api/utilisateurs/declare_connexion_groupe/$1/$2';
$route['api/utilisateurs/(:num)/groupes/(:num)']["DELETE"] = 'api/utilisateurs/remove_connexion_groupe/$1/$2';

$route['api/utilisateurs/(:num)/avis'] = 'api/utilisateurs/U_avis/$1';
$route['api/utilisateurs/(:num)/avis/(:num)'] = 'api/utilisateurs/U_avis/$1/$2';



// Albums
$route['api/albums/(:num)/titres'] = 'api/albums/titre/$1';


// Styles
$route['api/styles'] = 'api/styles';


// Evènements
$route['api/evenements'] = 'api/evenements';
$route['api/evenements/(:num)'] = 'api/evenements/detail/$1';


// Versions
$route['api/versions'] = 'api/versions';
$route['api/versions/updates'] = 'api/versions/updates';


// Localisation
$route['api/localisations'] = "api/localisations";


// Images
$route['api/images/no_image.png'] = 'api/images/noimage';
$route['api/images/(:any)/(:any)'] = 'api/images/$1/$2';


// Tout refuser par défaut
$route['(.*)'] = "api/apierror";