<?php

namespace CrazyGoat\SlimReactor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use React\Stream\ReadableResourceStream;
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
     * @var string|null
     */
    private $staticContentPath = null;

    /**
     * @var bool
     */
    private $convertToSlim = true;

    public function __construct(?App $app = null, array $options = [])
    {
        $this->app = $app ?? new App();
        $options = $this->mergeOptions($options);
        $this->loop = ($options['loopInterface'] instanceof LoopInterface) ?
            $options['loopInterface'] : Factory::create();
        $this->staticContentPath = !is_null($options['staticContentPath']) ?
            realpath(rtrim($options['staticContentPath'], DIRECTORY_SEPARATOR)) : null;
        $this->convertToSlim = $options['convertToSlim'];
        $this->createServer($options['socket']);
    }

    /**
     * @return SocketServer
     */
    public function getSocket()
    {
        return $this->socket;
    }

    private function mergeOptions(array $options) : array
    {
        return array_merge(
            [
                'socket' => '0.0.0.0:0',
                'loopInterface' => null,
                'convertToSlim' => true,
                'staticContentPath' => null
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
            if ($this->staticContentPath && $response = $this->handleStaticFile($request)) {
                return $response;
            }

            list($request, $response) = $this->convertToSlim ?
                [$this->createSlimRequest($request), new Response()] :
                [$request, new \React\Http\Response()];

            return $this->app->process($request, $response);
        };
    }

    protected function handleStaticFile(ServerRequestInterface $request) : ?ResponseInterface
    {
        $path = trim($request->getUri()->getPath());
        if ($path == '/') {
            return null;
        }

        $path = realpath($this->staticContentPath.$path);

        // check if $path is in staticContentPath
        if (strrpos($path, $this->staticContentPath) === 0 && is_file($path) && is_readable($path)) {
            return new \React\Http\Response(
                200,
                [],
                new ReadableResourceStream(fopen($path,"r"), $this->loop)
            );
        }
        return null;
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

    /**
     * @return App
     */
    public function getApp(): App
    {
        return $this->app;
    }
}
