<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router Basic Functionality
 */
class RouterTest extends TestCase
{
    public function testBasicCheckAndMiddleware(): void
    {
        // Given
        $actualChecks = [];
        $acutalMiddlewares = [];

        $router = Router::make()
            ->addCheck(function ($request) use (&$actualChecks) {
                $actualChecks[] = 1;
                return true;
            })
            ->addMiddleware(function ($request, $handler) use (&$acutalMiddlewares) {
                $acutalMiddlewares[] = 1;
                return new Response(200);
            });

        $request = new ServerRequest(
            method: 'GET',
            uri: 'http://localhost',
        );

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals(
            expected: 200,
            actual: $response->getStatusCode(),
        );

        $this->assertEquals(
            expected: [1],
            actual: $actualChecks,
            message: 'Unexpected Checks',
        );

        $this->assertEquals(
            expected: [1],
            actual: $acutalMiddlewares,
            message: 'Unexpected Middlewares',
        );
    }

    public function testCheckStopsRouter(): void
    {
        // Given
        $actualChecks = [];
        $acutalMiddlewares = [];

        $router = Router::make()
            ->addCheck(function ($request) use (&$actualChecks) {
                $actualChecks[] = 1;
                return false;
            })
            ->addMiddleware(function ($request, $handler) use (&$acutalMiddlewares) {
                $acutalMiddlewares[] = 1;
                return new Response(200);
            });

        $request = new ServerRequest(
            method: 'GET',
            uri: 'http://localhost',
        );

        // When
        $this->expectExceptionMessage('Router has no Middlewares left to process.');
        $response = $router->handle($request);

        // Then
        // Already expected the exception

        $this->assertEquals(
            expected: [1],
            actual: $actualChecks,
            message: 'Unexpected Checks',
        );

        $this->assertEquals(
            expected: [],
            actual: $acutalMiddlewares,
            message: 'Unexpected Middlewares',
        );
    }

    public function testRegexNamedMatchSetsAttribute(): void
    {
        // Given
        $request = new ServerRequest(
            method: 'GET',
            uri: 'http://localhost/foo/bar/fizz/0451/buzz',
        );

        $router = Router::make()
            ->branch()
                ->hasPath('/\/foo\/bar\/fizz\/(?<id>[0-9]{4})\/buzz/i')
                ->hasAttributeWithValue('id', '0451')
                ->addMiddleware(new Response(100))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals(
            expected: 100,
            actual: $response->getStatusCode(),
        );
    }
}
