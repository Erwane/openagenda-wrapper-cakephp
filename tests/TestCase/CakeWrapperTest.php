<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Wrapper\Test\TestCase;

use Cake\Core\Exception\Exception;
use Cake\Http\Client;
use Cake\Http\Client\Request;
use Cake\Http\Client\Response;
use Cake\Http\Exception\HttpException;
use Laminas\Diactoros\Uri;
use OpenAgenda\Wrapper\CakeWrapper;
use OpenAgenda\Wrapper\HttpWrapperException;
use OpenAgenda\Wrapper\HttpWrapperInterface;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \OpenAgenda\Wrapper\CakeWrapper
 * @covers \OpenAgenda\Wrapper\CakeWrapper
 */
class CakeWrapperTest extends TestCase
{
    /**
     * @var (\Cake\Http\Client&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $http;

    /**
     * @var \Cake\Http\Client\Request
     */
    protected $request;

    /**
     * @var Uri
     */
    protected $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = $this->getMockBuilder(Client::class)
            ->onlyMethods(['head', 'get', 'post', 'patch', 'delete'])
            ->getMock();

        $this->request = new Request('https://example.com', Client\Message::METHOD_GET);
        $this->uri = new Uri('https://example.com');
    }

    public static function dataPrepareOptions(): array
    {
        $resource = fopen(__FILE__, 'r');

        return [
            [
                ['headers' => ['x-foo' => 'bar']],
                [],
                [
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'User-Agent' => HttpWrapperInterface::USER_AGENT,
                            'x-foo' => 'bar',
                        ],
                        'redirect' => false,
                    ],
                    [],
                ],
            ],
            [
                [],
                ['key' => 'value', 'other' => 23],
                [
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        ],
                        'type' => 'json',
                        'redirect' => false,
                    ],
                    json_encode(['key' => 'value', 'other' => 23]),
                ],
            ],
            [
                [],
                ['key' => 'value', 'image' => $resource],
                [
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        ],
                        'redirect' => false,
                    ],
                    ['key' => 'value', 'image' => $resource],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataPrepareOptions
     */
    public function testPrepareOptions($options, $data, $expected)
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        [$options, $data] = $wrapper->prepareOptions($options, $data);

        $this->assertEquals($expected, [$options, $data]);
    }

    public function testBuildUriFromUrl(): void
    {
        $wrapper = new CakeWrapper();
        $uri = $wrapper->buildUri('https://example.com');

        $this->assertInstanceOf(Uri::class, $uri);
    }

    public function testCoreException(): void
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        $this->http->expects($this->once())
            ->method('get')
            ->willThrowException(new Exception('error', 504));

        try {
            $wrapper->get($this->uri);
        } catch (HttpWrapperException $e) {
            $this->assertEquals('Wrapper GET request failed. error', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e->getPrevious());
            $this->assertNull($e->getRequest());
            $this->assertNull($e->getResponse());
        }
    }

    public function testHttpException(): void
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        $this->http->expects($this->once())
            ->method('get')
            ->willThrowException(new HttpException('error', 500));

        try {
            $wrapper->get($this->uri);
        } catch (HttpWrapperException $e) {
            $this->assertEquals('Wrapper GET request failed. error', $e->getMessage());
            $this->assertInstanceOf(HttpException::class, $e->getPrevious());
            $this->assertNull($e->getRequest());
            $this->assertNull($e->getResponse());
        }
    }

    public static function dataExceptions(): array
    {
        return [
            ['head'],
            ['get'],
            ['post'],
            ['patch'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider dataExceptions
     */
    public function testCallExceptions($method): void
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        $this->http->expects($this->once())
            ->method($method)
            ->willThrowException(new HttpException('error'));

        try {
            $wrapper->{$method}($this->uri, []);
        } catch (HttpWrapperException $e) {
            $this->assertEquals(sprintf('Wrapper %s request failed. error', strtoupper($method)), $e->getMessage());
            $this->assertInstanceOf(HttpException::class, $e->getPrevious());
        }
    }

    public function testMethodHead()
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        $this->http->expects($this->once())
            ->method('head')
            ->with(
                'https://example.com',
                [],
                [
                    'redirect' => false,
                    'headers' => [
                        'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        'Accept' => 'application/json',
                    ],
                ]
            )
            ->willReturn(new Response());

        $wrapper->head($this->uri);
    }

    public function testMethodGet()
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        $this->http->expects($this->once())
            ->method('get')
            ->with(
                'https://example.com',
                [],
                [
                    'redirect' => false,
                    'headers' => [
                        'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        'Accept' => 'application/json',
                    ],
                ]
            )
            ->willReturn(new Response());

        $wrapper->get($this->uri);
    }

    public function testMethodPost()
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);

        $this->http->expects($this->once())
            ->method('post')
            ->with(
                'https://example.com',
                json_encode(['foo' => 'bar']),
                [
                    'redirect' => false,
                    'type' => 'json',
                    'headers' => [
                        'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        'Accept' => 'application/json',
                        'x-foo' => 'bar',
                    ],
                ]
            )
            ->willReturn(new Response());

        $wrapper->post($this->uri, ['foo' => 'bar'], ['headers' => ['x-foo' => 'bar']]);
    }

    public function testMethodPatch()
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);
        $this->http->expects($this->once())
            ->method('patch')
            ->with(
                'https://example.com',
                json_encode(['foo' => 'bar']),
                [
                    'redirect' => false,
                    'type' => 'json',
                    'headers' => [
                        'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        'Accept' => 'application/json',
                        'x-foo' => 'bar',
                    ],
                ]
            )
            ->willReturn(new Response());

        $wrapper->patch($this->uri, ['foo' => 'bar'], ['headers' => ['x-foo' => 'bar']]);
    }

    public function testMethodDelete()
    {
        $wrapper = new CakeWrapper();
        $wrapper->setClient($this->http);
        $this->http->expects($this->once())
            ->method('delete')
            ->with(
                'https://example.com',
                [],
                [
                    'redirect' => false,
                    'headers' => [
                        'User-Agent' => HttpWrapperInterface::USER_AGENT,
                        'Accept' => 'application/json',
                        'x-foo' => 'bar',
                    ],
                ]
            )
            ->willReturn(new Response());

        $wrapper->delete($this->uri, ['headers' => ['x-foo' => 'bar']]);
    }
}
