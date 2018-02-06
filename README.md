# slim-reactor
React Http server with Slim 3 as request handler. Slim-reactor allows you to run your 
Slim 3 application faster than traditional php-fpm. This goal is achieved by skipping 
the expensive bootstrap of PHP and framework. This is just a lib, witch can be used in skeleton 
like [slim-reactor-skeleton](https://github.com/crazy-goat/slim-reactor-skeleton). You will 
also need process manager like this one [goatherd](https://github.com/crazy-goat/goatherd).

## Installation

The easiest way to install slim-reactor is download it using composer

    composer require crazygoat/slim-reactor 

## Usage example

Below is minimal code to run slim inside react http server

    $app = new App(); // vanilla slim app see limitation
    
    //or 
    
    $app = new SlimReactorApp(); // 
    
    //some app configuration here
    
    $slimReactor  = new SlimReactor($app);
    $slimReactor->run();

More examples you will find in [examples directory](https://github.com/crazy-goat/slim-reactor/tree/master/examples).

### Known vanilla Slim App limitations
The vanilla Slim App (`Slim\App`) was not designed to run as part of React Http server so it has some usage limitations.

 * You can't use optional parameters in routes. Slim `Route` will remember optional attributes between request.
 Once optional attribute is in request, next request without this attribute will be still in route. **Fixed in SlimReactor**.

Most of these problem were fixed in `SlimReactorApp` and this class is prefered to be used with `SlimReactor`.

## Configuration
You can change default parameter, by passing second parameter to ``SlimReactor`` constructor.

    $slimReactor = new SlimReactor(
        $app,
        [
            // options here
        ]
    );

Available options:
 * `socket` - socket address, to listening on localhost interface on port 80 set this valut to `127.0.0.1:80`. 
 Default value is: `0.0.0.0:0`
 * `loopInterface` - option to pass you loop interface. If this option is not set, SlimRector will create it's own 
 instance of loop interface. Default value `null`.
 * `convertToSlim` - boolean option to convert PSR-7 request/response to Slim Request/Respnse classes. 
 This is required to set to `true` if your code use internal slim function like `Response->withJson()`.
 By default it's set to `true`. If we are sure, that your code is not using Slim internal functions setting 
 this option to `false` can signify speedup your application.
 * `staticContentPath` - path (can be relative) to static content directory. If set, then SlimReactor will try to
 serve static file if exists in `staticContentPath` directory. If file not exists SlimReactor will try to find matching 
 route. Default value `null`.