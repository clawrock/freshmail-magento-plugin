<?php

namespace Virtua\FreshMail\Model\FreshMail;

use FreshMail\Api\Client\FreshMailApiClient;
use Virtua\FreshMail\Logger\Logger;
use FreshMail\Api\Client\Response\HttpResponse;

class APiV3 extends FreshMailApiClient
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Logger $logger,
        $bearerToken = ''
    ) {
        $this->logger = $logger;
        parent::__construct($bearerToken);
    }

    public function post(string $uri, \JsonSerializable $data): HttpResponse
    {
        $this->logger->logIfDebugModeOn('send request to - ' . $uri);
        try {
            $response = $this->requestExecutor->post($uri, $data);
            $this->logger->logIfDebugModeOn('response: ' . $response->getJson());
        } catch (\Exception $e) {
            $this->logger->error('there was an error during request call to - '. $uri);
            $this->logger->error('request params - ' . var_export($data, true));
            $this->logger->error($e->getMessage());
            throw $e;
        }

        return $response;
    }
}
