<?php

namespace BarretStorck\ServerRequestRouter\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 *
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

    public static function provideTestHasMethod(): array
    {
        return [
            'GET' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'POST' => [
                'request' => new ServerRequest('POST', 'http://localhost'),
                'expect' => 101,
            ],
            'PUT' => [
                'request' => new ServerRequest('PUT', 'http://localhost'),
                'expect' => 101,
            ],
            'DELETE' => [
                'request' => new ServerRequest('DELETE', 'http://localhost'),
                'expect' => 100,
            ],
            'FOOBAR' => [
                'request' => new ServerRequest('FOOBAR', 'http://localhost'),
                'expect' => 500,
            ],
        ];
    }

    #[DataProvider('provideTestHasMethod')]
    public function testHasMethod($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasMethod('GET', 'DELETE')
                ->addMiddleware(new Response(100))
            ->parent()
            ->branch()
                ->hasMethod('/^P/')
                ->addMiddleware(new Response(101))
            ->parent()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }

    public static function provideTestHasHost(): array
    {
        return [
            'example.com' => [
                'request' => new ServerRequest('GET', 'http://example.com'),
                'expect' => 100,
            ],
            'microsoft.com' => [
                'request' => new ServerRequest('GET', 'http://microsoft.com'),
                'expect' => 100,
            ],
            'mozilla.net' => [
                'request' => new ServerRequest('GET', 'http://mozilla.net'),
                'expect' => 101,
            ],
            'php.net' => [
                'request' => new ServerRequest('GET', 'http://php.net'),
                'expect' => 101,
            ],
            'google.com' => [
                'request' => new ServerRequest('GET', 'http://google.com'),
                'expect' => 500,
            ],
        ];
    }

    #[DataProvider('provideTestHasHost')]
    public function testHasHost($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasHost('example.com', 'microsoft.com')
                ->addMiddleware(new Response(100))
            ->parent()
            ->branch()
                ->hasHost('/\.net$/')
                ->addMiddleware(new Response(101))
            ->parent()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }

    public static function provideTestHasBody(): array
    {
        return [
            'Yes' => [
                'request' => new ServerRequest('GET', 'http://localhost', [], 'Hello world!'),
                'expect' => 100,
            ],
            'No' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 500,
            ],
        ];
    }

    #[DataProvider('provideTestHasBody')]
    public function testHasBody($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasBody()
                ->addMiddleware(new Response(100))
            ->parent()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
