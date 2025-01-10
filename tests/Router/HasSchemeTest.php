<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasScheme()
 */
class hasSchemeTest extends TestCase
{
    public static function provideTestHasScheme(): array
    {
        return [
            'HTTP' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'HTTPS' => [
                'request' => new ServerRequest('GET', 'https://localhost'),
                'expect' => 101,
            ],
            'FTP' => [
                'request' => new ServerRequest('GET', 'ftp://localhost'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasScheme()
     * @dataProvider provideTestHasScheme
     */
    public function testHasScheme($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasScheme('HTTP')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasScheme('/^HTTPS$/i')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
