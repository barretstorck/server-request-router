<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasPath()
 */
class HasPathTest extends TestCase
{
    public static function provideTestHasPath(): array
    {
        return [
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            '/foo' => [
                'request' => new ServerRequest('GET', 'http://localhost/foo'),
                'expect' => 100,
            ],
            '/foo/bar' => [
                'request' => new ServerRequest('GET', 'http://localhost/foo/bar'),
                'expect' => 101,
            ],
            '/fizz/buzz' => [
                'request' => new ServerRequest('GET', 'http://localhost/fizz/buzz'),
                'expect' => 100,
            ],
            '/fizz/bar' => [
                'request' => new ServerRequest('GET', 'http://localhost/fizz/bar'),
                'expect' => 101,
            ],
            '/cat' => [
                'request' => new ServerRequest('GET', 'http://localhost/cat'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasPath()
     * @dataProvider provideTestHasPath
     */
    public function testHasPath($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasPath('', '/foo', '/fizz/buzz')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasPath('/\/bar$/')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
