<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasQuery()
 */
class HasQueryTest extends TestCase
{
    public static function provideTestHasQuery(): array
    {
        return [
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'foo=1' => [
                'request' => new ServerRequest('GET', 'http://localhost?foo=1'),
                'expect' => 100,
            ],
            'foo=1&bar=2' => [
                'request' => new ServerRequest('GET', 'http://localhost?foo=1&bar=2'),
                'expect' => 101,
            ],
            'fizz=3&buzz=4' => [
                'request' => new ServerRequest('GET', 'http://localhost?fizz=3&buzz=4'),
                'expect' => 100,
            ],
            'fizz=3&bar=2' => [
                'request' => new ServerRequest('GET', 'http://localhost?fizz=3&bar=2'),
                'expect' => 101,
            ],
            'cat=5' => [
                'request' => new ServerRequest('GET', 'http://localhost?cat=5'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasQuery()
     * @dataProvider provideTestHasQuery
     */
    public function testHasQuery($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasQuery('', 'foo=1', 'fizz=3&buzz=4')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasQuery('/bar=2/')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
