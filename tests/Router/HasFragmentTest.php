<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasFragment()
 */
class HasFragmentTest extends TestCase
{
    public static function provideTestHasFragment(): array
    {
        return [
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'apple' => [
                'request' => new ServerRequest('GET', 'http://localhost/#apple'),
                'expect' => 100,
            ],
            'foo' => [
                'request' => new ServerRequest('GET', 'http://localhost/#foo'),
                'expect' => 101,
            ],
            'foobar' => [
                'request' => new ServerRequest('GET', 'http://localhost/#foobar'),
                'expect' => 101,
            ],
            'cat' => [
                'request' => new ServerRequest('GET', 'http://localhost/#cat'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasFragment()
     * @dataProvider provideTestHasFragment
     */
    public function testHasFragment($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasFragment('', 'apple')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasFragment('/^foo/')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
