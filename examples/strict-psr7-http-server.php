#!/usr/bin/env php
<?php

use CrazyGoat\SlimReactor\SlimReactor;
use Psr\Http\Message\{
    ResponseInterface, ServerRequestInterface
};

include '../vendor/autoload.php';

$slimReactor = new SlimReactor(
    null,                                       //null - slim-reactor will create own instance of slim App
    [
        'convertToSlim' => false,               //do not convert vanilla PSR-7 to slim PSR-7
        'socket' => $argv[1] ?? '0.0.0.0:8080'  //pass socket as argument or user default
    ]
);
$address = $slimReactor->getSocket()->getAddress();

$slimReactor->getApp()->get(
    '/',
    function (ServerRequestInterface $request, ResponseInterface $response, $args) use ($address) {
        $response->getBody()->write('Hello World from ' . $address . '!');
        return $request;
    }
);

echo 'Server is listening on socket: ' . $address . "\n";
$slimReactor->run();
