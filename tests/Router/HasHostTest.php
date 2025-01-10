<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasHost()
 */
class HasHostTest extends TestCase
{
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

    /**
     * @testdox Router::hasHost()
     * @dataProvider provideTestHasHost
     */
    public function testHasHost($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasHost('example.com', 'microsoft.com')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasHost('/\.net$/')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
