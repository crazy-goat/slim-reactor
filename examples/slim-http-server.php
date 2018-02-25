#!/usr/bin/env php
<?php

use CrazyGoat\SlimReactor\SlimReactor;
use Slim\{App, Http\Request, Http\Response};

include '../vendor/autoload.php';

$slimReactor  = new SlimReactor();
$slimReactor->getApp()->get('/', function (Request $request, Response $response, $args) {
    return $response->write('Hello World!');
});

echo 'Server is listening on socket: '.$slimReactor->getSocket()->getAddress()."\n";

$slimReactor->run();
