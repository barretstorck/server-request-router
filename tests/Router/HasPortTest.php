<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasPort()
 */
class HasPortTest extends TestCase
{
    public static function provideTestHasPort(): array
    {
        return [
            'http://localhost' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'http://localhost:80' => [
                'request' => new ServerRequest('GET', 'http://localhost:80'),
                'expect' => 100,
            ],
            'https://localhost' => [
                'request' => new ServerRequest('GET', 'https://localhost'),
                'expect' => 101,
            ],
            'https://localhost:444' => [
                'request' => new ServerRequest('GET', 'https://localhost:444'),
                'expect' => 101,
            ],
            'ftp://localhost' => [
                'request' => new ServerRequest('GET', 'ftp://localhost'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasPort()
     * @dataProvider provideTestHasPort
     */
    public function testHasPort($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasPort(80)
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasPort('/^4[0-9]{2}$/')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
