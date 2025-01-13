<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasAttribute()
 */
class HasAttributeTest extends TestCase
{
    public static function provideTestHasAttribute(): array
    {
        return [
            'hit' => [
                'request' => (new ServerRequest('GET', 'http://localhost'))->withAttribute('foo', 'abc'),
                'expect' => 100,
            ],
            'miss' => [
                'request' => (new ServerRequest('GET', 'http://localhost'))->withAttribute('bar', 'abc'),
                'expect' => 500,
            ],
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasAttribute()
     * @dataProvider provideTestHasAttribute
     */
    public function testHasAttribute($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasAttribute() // Ensure that no parameters is handled correctly
                ->hasAttribute('foo')
                ->addMiddleware(new Response(100))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
