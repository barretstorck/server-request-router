<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasAuthority()
 */
class HasAuthorityTest extends TestCase
{
    public static function provideTestHasAuthority(): array
    {
        return [
            'localhost' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 100,
            ],
            'localhost:80' => [
                'request' => new ServerRequest('GET', 'http://localhost:80'),
                'expect' => 100,
            ],
            'user@localhost' => [
                'request' => new ServerRequest('GET', 'http://user@localhost'),
                'expect' => 101,
            ],
            'user@localhost:80' => [
                'request' => new ServerRequest('GET', 'http://user@localhost:80'),
                'expect' => 101,
            ],
            'foo@bar:80' => [
                'request' => new ServerRequest('GET', 'http://foo@bar:80'),
                'expect' => 500,
            ],
        ];
    }

    /**
     * @testdox Router::hasAuthority()
     * @dataProvider provideTestHasAuthority
     */
    public function testHasAuthority($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasAuthority() // Ensure that no parameters is handled correctly
                ->hasAuthority('localhost', 'localhost:80')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasAuthority('/^user\@/i')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
