<?php
declare(strict_types=1);

namespace OpenAgenda\Wrapper;

use Cake\Core\Exception\Exception;
use Cake\Http\Client;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * CakePHP Wrapper
 */
class CakeWrapper extends HttpWrapper
{
    /**
     * @var \Cake\Http\Client|\Psr\Http\Client\ClientInterface
     */
    protected $http;

    /**
     * {@inheritDoc}
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(array $params = [])
    {
        $this->http = new Client($params);
    }

    /**
     * Set client in wrapper. Useful for unit tests.
     *
     * @param \Cake\Http\Client $client CakePHP client
     * @return void
     */
    public function setClient(Client $client): void
    {
        $this->http = $client;
    }

    /**
     * Prepare request options.
     *
     * @param array $options Request options.
     * @param array $data Request data.
     * @return array
     */
    public function prepareOptions(array $options, array $data = []): array
    {
        $options['redirect'] = false;
        $options['headers']['Accept'] = 'application/json';
        $options['headers']['User-Agent'] = HttpWrapperInterface::USER_AGENT;

        if ($data) {
            // Has resource (file)
            $resources = array_filter($data, function ($value) {
                return is_resource($value);
            });

            if (!$resources) {
                $options['type'] = 'json';
                $data = json_encode($data);
            }
        }

        return [$options, $data];
    }

    /**
     * Build uri.
     *
     * @param \Psr\Http\Message\UriInterface|string $url Url as string or UriInterface
     * @return \Psr\Http\Message\UriInterface|\Laminas\Diactoros\Uri
     */
    public function buildUri($url): UriInterface
    {
        $uri = $url;
        if (is_string($url)) {
            $uri = new Uri($url);
        }

        return $uri;
    }

    /**
     * Call client request and handle exceptions.
     *
     * @param string $method Request method
     * @param \Laminas\Diactoros\Uri $uri Request URI
     * @param array $data Request data
     * @param array $options Request params
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    protected function _request(string $method, Uri $uri, $data = [], array $options = []): ResponseInterface
    {
        try {
            /**
             * @uses \Cake\Http\Client::head()
             * @uses \Cake\Http\Client::get()
             * @uses \Cake\Http\Client::post()
             * @uses \Cake\Http\Client::patch()
             * @uses \Cake\Http\Client::delete()
             */
            return $this->http->$method((string)$uri, $data, $options);
        } catch (Exception $e) {
            $message = sprintf('Wrapper %s request failed. %s', strtoupper($method), $e->getMessage());
            throw new HttpWrapperException($message, $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function head($uri, array $params = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        [$options, ] = $this->prepareOptions($params);

        return $this->_request(__FUNCTION__, $uri, [], $options);
    }

    /**
     * @inheritDoc
     */
    public function get($uri, array $params = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        [$options, ] = $this->prepareOptions($params);

        return $this->_request(__FUNCTION__, $uri, [], $options);
    }

    /**
     * @inheritDoc
     */
    public function post($uri, array $data, array $params = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        [$options, $data] = $this->prepareOptions($params, $data);

        return $this->_request(__FUNCTION__, $uri, $data, $options);
    }

    /**
     * @inheritDoc
     */
    public function patch($uri, array $data, array $params = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        [$options, $data] = $this->prepareOptions($params, $data);

        return $this->_request(__FUNCTION__, $uri, $data, $options);
    }

    /**
     * @inheritDoc
     */
    public function delete($uri, array $params = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        [$options, ] = $this->prepareOptions($params);

        return $this->_request(__FUNCTION__, $uri, [], $options);
    }
}
