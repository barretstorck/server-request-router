<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasBodySmallerThan()
 */
class HasBodySmallerThanTest extends TestCase
{
    public static function provideTestHasBodySmallerThan(): array
    {
        return [
            'Yes' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'No' => [
                'request' => new ServerRequest('GET', 'http://localhost', [], 'Hello world!'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasBodySmallerThan()
     * @dataProvider provideTestHasBodySmallerThan
     */
    public function testHasBodySmallerThan($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasBodySmallerThan(4)
                ->addMiddleware(new Response(100))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
