<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail;

use Virtua\FreshMail\Model\GetBearerTokenForListHash;
use FreshMail\ApiV2\Client;
use FreshMail\ApiV2\ConnectionException;
use FreshMail\ApiV2\ServerException;
use FreshMail\ApiV2\UnauthorizedException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Serialize\Serializer\Json;
use Virtua\FreshMail\Logger\Logger;

class ApiV2 extends Client
{
    /** @var string */
    private $bearerToken;
    /** @var Json */
    private $json;
    /** @var GetBearerTokenForListHash */
    private $getBearerTokenForListHash;
    /** @var \Virtua\FreshMail\Logger\Logger */
    private $logger;
    /** @var \GuzzleHttp\Client */
    private $guzzle;

    public function __construct(
        Json $json,
        GetBearerTokenForListHash $getBearerTokenForListHash,
        Logger $logger,
        $bearerToken = ''
    ) {
        parent::__construct($bearerToken);
        $this->bearerToken = $bearerToken;
        $this->json = $json;
        $this->getBearerTokenForListHash = $getBearerTokenForListHash;
        $this->logger = $logger;
        $this->guzzle = new \GuzzleHttp\Client();
    }

    public function doRequest(string $uri, array $params = []): array
    {
        $this->logger->logIfDebugModeOn('send request to - ' . $uri);
        try {
            $response = $this->call($uri, $params);
            $this->logger->logIfDebugModeOn('response: ' . $this->json->serialize($response));
        } catch (\Exception $e) {
            $this->logger->error('there was an error during request call to - '. $uri);
            $this->logger->error('request params - ' . var_export($params, true));
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $response;
    }

    public function call(string $uri, array $params = []): array
    {
        try {
            $method = ($params) ? 'POST' : 'GET';

            $response = $this->guzzle->request($method, $uri, $this->getRequestOptions($params));
            $rawResponse = $response->getBody()->getContents();
            /** @var array $jsonResponse */
            $jsonResponse = $this->json->unserialize($rawResponse);

            if (!$jsonResponse) {
                throw new ServerException(
                    sprintf(
                        'Unable to parse response from server, raw response: %s',
                        $rawResponse
                    )
                );
            }

            return $jsonResponse;
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            if ($exception->getCode() == 401) {
                throw new UnauthorizedException('Request unauthorized');
            }

            throw new \FreshMail\ApiV2\ClientException(sprintf(
                'Connection error, error message: %s',
                $exception->getResponse()->getBody()->getContents()
            ));
        } catch (\GuzzleHttp\Exception\ConnectException $exception) {
            throw new ConnectionException(sprintf('Connection error, error message: '.$exception->getMessage()));
        }
    }

    private function getRequestOptions(array $requestData): array
    {
        if (!empty($requestData['list'])
            && ($token = $this->getBearerTokenForListHash->execute($requestData['list']))) {
            $this->bearerToken = $token;
        }

        return [
            'base_uri' => sprintf('%s://%s/%s/', self::SCHEME, self::HOST, self::PREFIX),
            RequestOptions::BODY => $this->json->serialize($requestData),
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'User-Agent' => $this->createUserAgent()
            ]
        ];
    }

    private function createUserAgent(): string
    {
        return
            sprintf(
                'freshmail/php-api-v2-client:%s;guzzle:%s;php:%s;interface:%s',
                self::VERSION,
                self::CLIENT_VERSION,
                PHP_VERSION,
                php_sapi_name()
            );
    }
}
