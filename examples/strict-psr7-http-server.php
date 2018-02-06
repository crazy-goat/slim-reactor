<?php

use CrazyGoat\SlimReactor\SlimReactor;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Slim\App;

include '../vendor/autoload.php';

$app = new App();
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $response->getBody()->write('Hello World!');
    return $request;
});

$slimReactor  = new SlimReactor($app, ['convertToSlim' => false]);
echo 'Server is listening on socket: '.$slimReactor->getSocket()->getAddress()."\n";

$slimReactor->run();
