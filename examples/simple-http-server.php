<?php
/**
 * Usage of ServerRequestInterface and ResponseInterface in Closure

 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

include '../vendor/autoload.php';

$app = new \Slim\App();
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    return $response->write('Hello World!');
});

$slimReactor  = new \CrazyGoat\SlimReactor\SlimReactor($app, isset($argv[1]) ? $argv[1] : '0.0.0.0:0');
$slimReactor->run();

