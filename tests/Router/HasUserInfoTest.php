<?php

namespace BarretStorck\ServerRequestRouter\Tests\Router;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use BarretStorck\ServerRequestRouter\Router;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Exception;

/**
 * @testdox Router::hasUserInfo()
 */
class HasUserInfoTest extends TestCase
{
    public static function provideTestHasUserInfo(): array
    {
        return [
            'none' => [
                'request' => new ServerRequest('GET', 'http://localhost'),
                'expect' => 500,
            ],
            'user' => [
                'request' => new ServerRequest('GET', 'http://user@localhost'),
                'expect' => 100,
            ],
            'user:password' => [
                'request' => new ServerRequest('GET', 'http://user:password@localhost'),
                'expect' => 101,
            ],
            'foo:bar' => [
                'request' => new ServerRequest('GET', 'http://foo:bar@localhost'),
                'expect' => 101,
            ],
            'fizz:buzz' => [
                'request' => new ServerRequest('GET', 'http://fizz:buzz@localhost'),
                'expect' => 101,
            ],
        ];
    }

    /**
     * @testdox Router::hasUserInfo()
     * @dataProvider provideTestHasUserInfo
     */
    public function testHasUserInfo($request, $expect): void
    {
        // Given
        $router = Router::make()
            ->branch()
                ->hasUserInfo('user')
                ->addMiddleware(new Response(100))
            ->root()
            ->branch()
                ->hasUserInfo('/\:[a-z0-9]+$/i')
                ->addMiddleware(new Response(101))
            ->root()
            ->addMiddleware(new Response(500));

        // When
        $response = $router->handle($request);

        // Then
        $this->assertEquals($expect, $response->getStatusCode());
    }
}
