<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasMethod()
 */
class HasMethodTest extends TestCase
{
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

    /**
     * @testdox Router::hasMethod()
     * @dataProvider provideTestHasMethod
     */
    public function testHasMethod($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasMethod('GET', 'DELETE')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasMethod('/^P/')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
