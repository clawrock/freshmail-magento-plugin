<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail;

use FreshMail\ApiV2\Client;
use Virtua\FreshMail\Logger\Logger;

class ApiV2 extends Client
{
    private $logger;

    public function __construct(
        Logger $logger,
        $bearerToken = ''
    ) {
        $this->logger = $logger;
        parent::__construct($bearerToken);
    }

    public function doRequest(string $uri, array $params = []): array
    {
        // todo add try catch to catch exception and log the problems
        $this->logger->logIfDebugModeOn('send request to - ' . $uri);
        try {
            $response = parent::doRequest($uri, $params);
            $this->logger->logIfDebugModeOn('response: ' . json_encode($response));
        } catch (\Exception $e) {
            $this->logger->error('there was an error during request call to - '. $uri);
            $this->logger->error('request params - ' . var_export($params, true));
            $this->logger->error($e->getMessage());
            throw $e;
        }

        return $response;
    }
}