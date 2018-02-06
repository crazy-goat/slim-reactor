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

    /**
     * @var array
     */
    private $options;

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;
        $this->options = $this->getOptions($options);
        $this->loop = ($this->options['loopInterface'] instanceof LoopInterface) ?
            $this->options['loopInterface'] : Factory::create();
        $this->createServer($this->options['socket']);
    }

    /**
     * @return SocketServer
     */
    public function getSocket()
    {
        return $this->socket;
    }

    private function getOptions(array $options) : array
    {
        return array_merge(
            [
                'socket' => '0.0.0.0:0',
                'loopInterface' => null,
                'convertToSlim' => true
            ],
            $options
        );
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
            $request =  $this->options['convertToSlim'] ? $this->createSlimRequest($request) : $request;
            $response = $this->options['convertToSlim'] ? new Response() : new \React\Http\Response();
            return $this->app->process($request, $response);
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
