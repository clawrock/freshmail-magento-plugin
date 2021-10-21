<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Virtua\FreshMail\Model\FreshMail\ApiV2Factory;

class GetFreshMailTemplates
{
    /** @var ApiV2Factory */
    private $apiV2Factory;

    public function __construct(
        ApiV2Factory $apiV2Factory
    ) {
        $this->apiV2Factory = $apiV2Factory;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function execute(): array
    {
        $api = $this->apiV2Factory->create();
        $response = $api->doRequest('https://api.freshmail.com/rest/templates/lists');

        return $response['data'] ?? [];
    }
}
