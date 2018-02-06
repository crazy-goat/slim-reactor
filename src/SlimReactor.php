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
use Slim\Http\Response;

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

    /**
     * @var SocketServer
     */
    private $socket;

    public function __construct(App $app, string $uri, ?LoopInterface $loop = null)
    {
        $this->app = $app;
        $this->loop = ($loop instanceof LoopInterface) ? $loop : Factory::create();
        $this->createServer($uri);
    }

    /**
     * @return SocketServer
     */
    public function getSocket()
    {
        return $this->socket;
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
        $this->server = new HttpServer($this->getCallback());
        $this->socket = new SocketServer($uri, $this->loop);
        $this->server->listen($this->socket);
    }

    /**
     * @return \Closure
     */
    private function getCallback()
    {
        return function (ServerRequestInterface $request) {
            return $this->app->process($this->createSlimRequest($request), new Response());
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
