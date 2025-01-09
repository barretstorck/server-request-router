<?php

namespace BarretStorck\ServerRequestRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A middleware that executes a given function when called. This allows for
 * anonymous functions to be useable as middlewares. The function will be
 * expected to accept and return the same paramters as the
 * MiddlewareInterface::process() function.
 */
class FunctionMiddleware implements MiddlewareInterface
{
    protected $function;

    /**
     *
     */
    public function __construct(callable $function)
    {
        $this->function = $function;
    }

    /**
     *
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return call_user_func($this->function, $request, $handler);
    }
}
