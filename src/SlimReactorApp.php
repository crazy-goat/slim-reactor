<?php

namespace CrazyGoat\SlimReactor;

use FastRoute\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Interfaces\RouterInterface;

class SlimReactorApp extends App
{
    protected function dispatchRouterAndPrepareRoute(ServerRequestInterface $request, RouterInterface $router)
    {
        $routeInfo = $router->dispatch($request);

        if ($routeInfo[0] === Dispatcher::FOUND) {

            $routeArguments = [];
            foreach ($routeInfo[2] as $k => $v) {
                $routeArguments[$k] = urldecode($v);
            }

            $route = $router->lookupRoute($routeInfo[1]);

            // ---------------- hack begin -------------
            // Cleanup between requests
            $route->setArguments([]);
            // ---------------- hack end ---------------

            $route->prepare($request, $routeArguments);

            // add route to the request's attributes in case a middleware or handler needs access to the route
            $request = $request->withAttribute('route', $route);
        }

        $routeInfo['request'] = [$request->getMethod(), (string) $request->getUri()];

        return $request->withAttribute('routeInfo', $routeInfo);
    }
}
