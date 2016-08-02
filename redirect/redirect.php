<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

error_reporting(E_ALL^E_DEPRECATED);

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/constants.php';

$app = new \Slim\App;
$app->get('{path:.*}', function (Request $request, Response $response) {
    $params = $request->getQueryParams();
    global $config;
    $config = new \Config();
    switch ($params['archiAffichage']) {
        case 'adresseDetail':
            $id = intval($params['archiIdAdresse']);
            $a = new \archiAdresse();
            $addressInfo = $a->getArrayAdresseFromIdAdresse($id);
            $return = strip_tags(
                $a->getIntituleAdresseFrom(
                    $id,
                    'idAdresse',
                    array(
                        'noHTML'=>true, 'noQuartier'=>true, 'noSousQuartier'=>true, 'noVille'=>true,
                        'displayFirstTitreAdresse'=>true,
                        'setSeparatorAfterTitle'=>'#'
                    )
                )
            ).' ('.$addressInfo['nomVille'].')';
            $return = explode('#', $return);
            $name = $return[0];
            $name = str_replace("l' ", "l'", $name);
            $name = str_replace("d' ", "d'", $name);
            $name = trim($name, '.');
            return $response->withRedirect('index.php/Adresse:'.$name, 301);
        case 'evenementListe':
            switch ($params['selection']) {
                case 'personne':
                    $person = new \ArchiPersonne(intval($params['id']));
                    return $response->withRedirect('index.php/Personne:'.$person->prenom.' '.$person->nom, 301);
            }
    }
});
$app->run();
