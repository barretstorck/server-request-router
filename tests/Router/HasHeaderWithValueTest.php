<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasHeaderWithValue()
 */
class HasHeaderWithValueTest extends TestCase
{
    public static function provideTestHasHeaderWithValue(): array
    {
        return [
            'foo => bar' => [
                'request' => new ServerRequest('GET', 'http://localhost', ['foo' => 'bar']),
                'expect' => 100,
            ],
            'Content-Type => text/plain' => [
                'request' => new ServerRequest('GET', 'http://localhost', ['Content-Type' => 'text/plain']),
                'expect' => 101,
            ],
            'Content-Disposition => inline' => [
                'request' => new ServerRequest('GET', 'http://localhost', ['Content-Disposition' => 'inline']),
                'expect' => 102,
            ],
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasHeaderWithValue()
     * @dataProvider provideTestHasHeaderWithValue
     */
    public function testHasHeaderWithValue($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasHeaderWithValue('anything') // Ensure that no parameters is handled correctly
                ->hasHeaderWithValue('foo', 'bar')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasHeaderWithValue('Content-Type', 'text/plain')
                ->addMiddleware(new Response(101))
            ->root()
            ->branch()
                ->hasHeaderWithValue('content-disposition', 'inline')
                ->addMiddleware(new Response(102))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
