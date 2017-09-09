<?php

declare(strict_types=1);

namespace unreal4u\TelegramAPI;

use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\Response;
use Amp\Promise;
use React\Promise\PromiseInterface;
use unreal4u\TelegramAPI\Exceptions\ClientException;
use unreal4u\TelegramAPI\InternalFunctionality\TelegramResponse;

class HttpClientRequestHandlerAmp implements RequestHandlerInterface {

    /**
     * @var DefaultClient
     */
    private $client;

    public function __construct() {
        $this->client = new DefaultClient;
    }

    /**
     * @param string $uri
     *
     * @return PromiseInterface
     */
    public function get(string $uri): PromiseInterface {
         $request = (new Request($uri));

         return $this->processRequest($request);
    }

    /**
     * @param string $uri
     * @param array $formFields
     *
     * @return PromiseInterface
     */
    public function post(string $uri, array $formFields): PromiseInterface {
        $request = new Request($uri, 'POST');

        if (!empty($formFields['headers']))
            $request = $request->withHeaders($formFields['headers']);

        if (!empty($formFields['body']))
            $request = $request->withBody($formFields['body']);

        return $this->processRequest($request);
    }

    /**
     * @param Request $request
     *
     * @return PromiseInterface
     */
    private function processRequest(Request $request) {
        return \Interop\React\Promise\adapt(\Amp\call(function () use ($request) {
            /** @var Response $response */
            $response = yield $this->client->request($request);

            if ($response->getStatus() >= 400)
                throw new ClientException($response->getReason(), $response->getStatus());

            return new TelegramResponse(yield $response->getBody(), $response->getHeaders());
        }));
    }
}
