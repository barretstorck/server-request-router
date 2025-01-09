<?php

namespace BarretStorck\ServerRequestRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

/**
 *
 */
class Router implements MiddlewareInterface, RequestHandlerInterface
{
    protected array $checks = [];
    protected int $position = 0;
    protected array $middlewares = [];
    protected null|Router $parent = null;

    /**
     * A static alias function for the constructor. This allows for stringing
     * multiple functions together more easily.
     * Instead of:
     *  (new Router())->addMiddleware(...);
     * you can use:
     *  Router::make()->addMiddleware(...);
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Implementation of the MiddlewareInterface::process() function.
     * If this router's checks pass for the given request
     * then this router's middlewares will be used
     * otherwise this router's middlewares will be skipped entirely.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setParent($handler);
        return $this->handle($request);
    }

    /**
     * Implementation of the RequestHandlerInterface::handle() function.
     * This function handles fetching the current middleware from the list and
     * passing the request to it to be handled.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $currentMiddleware = $this->middlewares[$this->position] ?? null;

        // If the Router hasn't already started using it's middlewares
        // (aka $this->position === 0)
        // and it should not handle the request
        // then attempt to pass handling to the parent if there is one.
        if (is_null($currentMiddleware) || (!$this->isStarted() && !$this->shouldHandleRequest($request))) {
            $parent = $this->parent();
            if (!$parent) {
                throw new Exception('Router has no Middlewares left to process.');
            }
            return $parent->handle($request);
        }

        $this->position++;
        return $currentMiddleware->process($request, $this);
    }

    /**
     * Returns true if the Router has already started processing middlewares.
     */
    public function isStarted(): bool
    {
        return $this->position > 0;
    }

    /**
     * Sets the parent Router if there is one. The parent
     */
    public function setParent(Router $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     *
     */
    public function parent(): null|Router
    {
        return $this->parent;
    }

    /**
     *
     */
    public function root(): static
    {
        $current = $this;
        while ($parent = $current->parent()) {
            $current = $parent;
        }

        return $current;
    }

    /**
     * Determine if the Router should handle the given Request. This is done by
     * passing the Request to each of the Router's check functions. If all the
     * check functions return a boolean True, then the Router should handle the
     * Request. If any of the check functions don't return a boolean True, then
     * the remaining check functions are skipped and the Router should not
     * handle the Request.
     */
    public function shouldHandleRequest(ServerRequestInterface $request): bool
    {
        // Loop over each check
        foreach ($this->checks as $check) {
            // Call each check's function
            // with the request passed as a parameter
            $result = call_user_func($check, $request);

            // If the function returned anything but a boolean "true"
            // then consider the check failed and return false to indicate
            // that this middleware should not handle the request.
            if ($result !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add a function to be used to check if the Router should handle a request.
     * The function will have a ServerRequestInterface object passed as it's
     * only parameter.
     */
    public function addCheck(callable ...$checks): self
    {
        $this->checks = array_merge($this->checks, $checks);
        return $this;
    }

    /**
     * Add one or more Middlewares to the Router's list. If the Router's checks
     * pass for a given Request, then that Request will be processed by it's
     * middlewares.
     */
    public function addMiddleware(callable|ResponseInterface|MiddlewareInterface ...$middlewares): self
    {
        foreach ($middlewares as $middleware) {
            // If we have a callable function given as a middleware
            // then wrap it within the FunctionMiddleware
            if (is_callable($middleware)) {
                $middleware = new FunctionMiddleware($middleware);
            }

            // If a response is hard coded
            // then set up a middleware that always returns it
            if ($middleware instanceof ResponseInterface) {
                $response = $middleware;
                $middleware = new FunctionMiddleware(function () use ($response) {
                    return $response;
                });
            }

            // Add the middleware to our list.
            $this->middlewares[] = $middleware;

            // If the middleware is another router
            // then establish parenthood
            if ($middleware instanceof static) {
                $middleware->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Creates a child Router to the current router and returns it. This allows
     * for more complex groupings of middlewares and checks.
     */
    public function branch(): static
    {
        $branch = new static();
        $this->addMiddleware($branch);
        return $branch;
    }

    /**
     * Adds a check function to the Router that will pass if a Request uses any
     * of the given HTTP methods.
     */
    public function hasMethod(string ...$inputs): self
    {
        if (empty($inputs)) {
            return $this;
        }

        return $this->addCheck(function ($request) use ($inputs) {
            $needleMethod = strtolower(trim($request->getMethod()));
            foreach ($inputs as $input) {
                $haystackMethod = strtolower(trim($input));
                if ($needleMethod === $haystackMethod) {
                    return true;
                }
            }
            return false;
        });
    }
}
