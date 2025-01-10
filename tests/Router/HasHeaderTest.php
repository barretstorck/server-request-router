<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasHeader()
 */
class HasHeaderTest extends TestCase
{
    public static function provideTestHasHeader(): array
    {
        return [
            'foo' => [
                'request' => new ServerRequest('GET', 'http://localhost', ['foo' => 'bar']),
                'expect' => 100,
            ],
            'Content-Type' => [
                'request' => new ServerRequest('GET', 'http://localhost', ['Content-Type' => 'text/plain']),
                'expect' => 100,
            ],
            'Content-Disposition' => [
                'request' => new ServerRequest('GET', 'http://localhost', ['Content-Disposition' => 'inline']),
                'expect' => 100,
            ],
            'empty' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasHeader()
     * @dataProvider provideTestHasHeader
     */
    public function testHasHeader($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasHeader() // Ensure that no parameters is handled correctly
                ->hasHeader('foo', 'Content-Type', 'content-disposition')
                ->addMiddleware(new Response(100))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
