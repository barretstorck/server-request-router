<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasAttributeWithValue()
 */
class HasAttributeWithValueTest extends TestCase
{
    public static function provideTestHasAttributeWithValue(): array
    {
        return [
            'hit' => [
                'request' => (new ServerRequest('GET', 'http://localhost'))->withAttribute('foo', 'abc'),
                'expect' => 100,
            ],
            'wrong value' => [
                'request' => (new ServerRequest('GET', 'http://localhost'))->withAttribute('foo', 'def'),
                'expect' => 500,
            ],
            'wrong attribute' => [
                'request' => (new ServerRequest('GET', 'http://localhost'))->withAttribute('bar', 'abc'),
                'expect' => 500,
            ],
            'both wrong' => [
                'request' => (new ServerRequest('GET', 'http://localhost'))->withAttribute('bar', 'def'),
                'expect' => 500,
            ],
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasAttributeWithValue()
     * @dataProvider provideTestHasAttributeWithValue
     */
    public function testHasAttributeWithValue($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasAttributeWithValue('foo') // Ensure that no parameters is handled correctly
                ->hasAttributeWithValue('foo', 'abc')
                ->addMiddleware(new Response(100))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
