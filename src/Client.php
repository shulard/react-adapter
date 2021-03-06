<?php

namespace Http\Adapter\React;

use Http\Client\HttpClient;
use Http\Client\HttpAsyncClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser as ReactBrowser;

/**
 * Client for the React promise implementation.
 *
 * @author Stéphane Hulard <s.hulard@chstudio.fr>
 */
class Client implements HttpClient, HttpAsyncClient
{
    /**
     * React HTTP client.
     *
     * @var ReactBrowser
     */
    private $client;

    /**
     * React event loop.
     *
     * @var LoopInterface
     */
    private $loop;

    /**
     * Initialize the React client.
     */
    public function __construct(
        LoopInterface $loop = null,
        ReactBrowser $client = null
    ) {
        if (null !== $client && null === $loop) {
            throw new \RuntimeException('You must give a LoopInterface instance with the Client');
        }

        $this->loop = $loop ?: ReactFactory::buildEventLoop();
        $this->client = $client ?: ReactFactory::buildHttpClient($this->loop);
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $promise = $this->sendAsyncRequest($request);

        return $promise->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        $promise = new Promise(
            $this->client->request(
                $request->getMethod(),
                $request->getUri(),
                $request->getHeaders(),
                $request->getBody()
            ),
            $this->loop,
            $request
        );

        return $promise;
    }
}
