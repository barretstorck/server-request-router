# Server Request Router
A PSR-15 compliant ServerRequestInterface router that allows for conditional
routing to different middlewares based on the properties of the ServerRequest.
This allows for only using middlewares when they are needed, and when the
middlewares are provided as [lazy objects](https://www.php.net/manual/en/language.oop5.lazy-objects.php)
then they can skip instantiation entirely if not needed, saving time and server
resources.

## Installation
```bash
composer require barretstorck/server-request-router
```

# Examples
## Hello world
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

## Echo value from URL path
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

## Larger example with API endpoints
Includes:
- Middlewares that are performed for all requests
    - ErrorCatcherMiddleware
    - MessageLoggerMiddleware
    - RateLimiterMiddleware
- A branch to handle when the request has contents in the body
    - Return a 413 "Content Too Large" response if the body is too big
    - Otherwise pass the request to an AntiMalwareMiddleware 
- A branch to handle the "/comments" API endpoint
    - GET method calls CommentsGetMiddleware
    - PUT method calls CommentsPutMiddleware
    - POST method extracts the comment ID from the URL path, sets it as an attribute on the Request, and calls CommentsPostMiddleware
    - DELETE method calls CommentsDeleteMiddleware
- A default response of 404 "Not Found" for all other requests
```php
<?php

use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

// Create the router.
$router = Router::make()

    // Add middlewares that will be used for all requests.
    ->addMiddleware(
        new ErrorCatcherMiddleware(), // Catch and handle any thrown errors
        new MessageLoggerMiddleware(), // Log all requests and responses
        new RateLimiterMiddleware(), // Limit the number of requests per minute
    )

    // Create a branch to handle if the request has a body.
    ->branch()
        ->hasBody()
        ->branch()
            // If the request has a body that is larger than 1GB, return a 413 error.
            ->hasBodyLargerThan(pow(1024, 3))
            ->addMiddleware(new Response(413, ['content-type' => 'text/plain'], 'Content Too Large'))
        ->parent()

        // If the request has a body that is an acceptable size, check for malware.
        ->addMiddleware(new AntiMalwareMiddleware())
    
    // Go to the root of the router
    ->root()

    // Add a branch to handle the /comments API
    ->branch()
        ->hasPath('/^\/comments/i')
        ->branch()
            // Handle GET /comments
            ->hasMethod('GET')
            ->addMiddleware(new CommentsGetMiddleware())
        ->parent()
        ->branch()
            // Handle PUT /comments
            ->hasMethod('PUT')
            ->addMiddleware(new CommentsPutMiddleware())
        ->parent()
        ->branch()
            // Fetch the comment_id from the path
            ->hasPath('/^\/comments\/(?<comment_id>\d+)$/i')
            ->branch()
                // Handle POST /comments/<comment_id>
                ->hasMethod('POST')
                ->addMiddleware(new CommentsPostMiddleware())
            ->parent()
            ->branch()
                // Handle DELETE /comments/<comment_id>
                ->hasMethod('DELETE')
                ->addMiddleware(new CommentsDeleteMiddleware())
    
    // Go back to the root of the router
    ->root()

    // Send a 404 response if no route was matched
    ->addMiddleware(new Response(404, ['content-type' => 'text/plain'], 'Not Found'));

$request = ServerRequest::fromGlobals();

$response = $router->handle($request);


$emitter = new SapiEmitter();

$emitter->emit($response);
```

The code above provides a Router that matches the following flow chart:
![Flow chart](docs/img/larger%20example.png)