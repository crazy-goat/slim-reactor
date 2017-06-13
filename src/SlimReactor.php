<?php

namespace CrazyGoat\SlimReactor;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;

class SlimReactor
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var HttpServer
     */
    private $server;

    /** @var LoopInterface */
    private $loop;

    public function __construct(App $app, string $uri)
    {
        $this->app = $app;
        $this->createServer($uri);
    }

    public function run() : void
    {
        $this->loop->run();
    }

    /**
     * @param string $uri
     */
    private function createServer(string $uri) : void
    {
        $this->loop = Factory::create();
        $this->server = new HttpServer($this->getGallback());
        $socket = new SocketServer($uri, $this->loop);
        $this->server->listen($socket);
    }

    /**
     * @return \Closure
     */
    private function getGallback()
    {
        return function (ServerRequestInterface $request) {
            return $this->app->process(
                $this->createSlimRequest($request),
                new \Slim\Http\Response()
            );
        };
    }

    /**
     * @param ServerRequestInterface $request
     * @return Request
     */
    private function createSlimRequest(ServerRequestInterface $request) : Request
    {
        /** @var Request $slimRequest */
        $slimRequest = Request::createFromEnvironment(Environment::mock())
            ->withMethod($request->getMethod())
            ->withAttributes($request->getAttributes())
            ->withUri($request->getUri())
            ->withCookieParams($request->getCookieParams())
            ->withBody($request->getBody())
            ->withUploadedFiles($request->getUploadedFiles());

        foreach ($request->getHeaders() as $key => $header) {
            $slimRequest = $slimRequest->withHeader($key, $header);
        }

        return $slimRequest;
    }
}
