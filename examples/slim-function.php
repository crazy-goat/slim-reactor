<?php
/**
 *
 */
use Slim\Http\Response;
use Slim\Http\Request;

include '../vendor/autoload.php';

$app = new \Slim\App();
$app->get('/', function (Request $request, Response $response, $args) {
    return $response->write('<html><body><h1>go to <a href="/hello/world">/hello/world</a> 
    to test router</h1></body></html>');
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    return $response->write("Hello, " . $args['name']);
});

$slimReactor  = new \CrazyGoat\SlimReactor\SlimReactor($app, isset($argv[1]) ? $argv[1] : '0.0.0.0:0');
$slimReactor->run();

