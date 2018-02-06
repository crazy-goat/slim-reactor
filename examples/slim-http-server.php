<?php

use CrazyGoat\SlimReactor\SlimReactor;
use Slim\{App, Http\Request, Http\Response};

include '../vendor/autoload.php';

$app = new App();
$app->get('/', function (Request $request, Response $response, $args) {
    return $response->write('Hello World!');
});

$slimReactor  = new SlimReactor($app);
echo 'Server is listening on socket: '.$slimReactor->getSocket()->getAddress()."\n";

$slimReactor->run();
