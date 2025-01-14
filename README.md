# Server Request Router
A PSR-15 compliant ServerRequestInterface router that allows for conditional
routing to different middlewares.


# Examples
### Hello world
```php
<?php

use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

// Create a new instance of the Router.
$router = new Router();

// Add middleware to the router.
// In this case, it's a single middleware that will be used for all requests.
$router->addMiddleware(
    new Response(200, ['Content-Type' => 'text/plain'], 'Hello world!'), // Define the response to always return.
);

// Create a ServerRequest object representing the current HTTP request,
// populated from PHP's global variables like $_SERVER, $_GET, $_POST, etc.
$request = ServerRequest::fromGlobals();

// Process the request through the router and obtain the corresponding Response object.
// Since we've only added one middleware that returns a response, this will always return that response.
$response = $router->handle($request);

// Create an instance of SapiEmitter, which will handle sending the response back to the client.
$emitter = new SapiEmitter();
// Send the response to the client, including headers and body.
$emitter->emit($response);
```

### Echo value from URL path
```php
<?php

use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

// Initialize the router.
$router = Router::make()
    // Start a new branch for specific paths.
    ->branch()
        // Define the path pattern this branch handles.
        // The pattern matches paths starting with "/echo/",
        // followed by a captured group named 'my_path_value'
        // consisting of 1 to 64 alphanumeric characters.
        // The 'i' flag makes the pattern case-insensitive.
        ->hasPath('/^\/echo\/(?<my_path_value>[a-z0-9]{1,64})$/i')
        // Add middleware for this specific path.
        // This anonymous function will be executed if the path matches.
        ->addMiddleware(function($request) {
            // Retrieve the 'my_path_value' captured from the path.
            $pathValue = $request->getAttribute('my_path_value');
            // Create a new Response object.
            // Status code 200 (OK), Content-Type header set to 'text/plain',
            // and the body is set to the captured path value.
            $response = new Response(200, ['Content-Type' => 'text/plain'], $pathValue);
            // Return the created response.
            return $response;
        })
    // Return to the root level of the router.
    ->root()
    // Add a default middleware at the root level.
    // This will be executed if no other branch matches.
    // It sets a default 404 (Not Found) response.
    ->addMiddleware(new Response(404));

// Create a ServerRequest object from the global PHP variables (e.g., $_SERVER, $_GET, $_POST).
$request = ServerRequest::fromGlobals();

// Handle the request using the router and get the corresponding response.
$response = $router->handle($request);

// Create a new SapiEmitter instance. This is responsible for sending the response to the client.
$emitter = new SapiEmitter();
// Emit the response. This sends the response headers and body to the client.
$emitter->emit($response);
```
```php
<?php

use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

// Initialize the router.
$router = Router::make()
    // Start a new branch for specific paths.
    ->branch()
        // Define the path pattern this branch handles.
        // The pattern matches paths starting with "/example/",
        // followed by a captured group named 'example_value'
        // consisting of 1 to 64 alphanumeric characters.
        // The 'i' flag makes the pattern case-insensitive.
        ->hasPath('/^\/example\/(?<example_value>[a-z0-9]{1,64})$/i')
        // Add middleware for this specific path.
        // This anonymous function will be executed if the path matches.
        ->addMiddleware(function($request) {
            // Retrieve the 'example_value' captured from the path.
            $pathValue = $request->getAttribute('example_value');
            // Create a new Response object.
            // Status code 200 (OK), Content-Type header set to 'text/plain',
            // and the body is set to the captured path value.
            $response = new Response(200, ['Content-Type' => 'text/plain'], "You have accessed: " . $pathValue);
            // Return the created response.
            return $response;
        })
    // Return to the root level of the router.
    ->root()
    // Add a default middleware at the root level.
    // This will be executed if no other branch matches.
    // It sets a default 404 (Not Found) response.
    ->addMiddleware(new Response(404));

// Create a ServerRequest object from the global PHP variables (e.g., $_SERVER, $_GET, $_POST).
$request = ServerRequest::fromGlobals();

// Handle the request using the router and get the corresponding response.
$response = $router->handle($request);

// Create a new SapiEmitter instance. This is responsible for sending the response to the client.
$emitter = new SapiEmitter();
// Emit the response. This sends the response headers and body to the client.
$emitter->emit($response);
```