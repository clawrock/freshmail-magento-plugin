<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Virtua\FreshMail\Model\GetBearerTokenForListHash;
use Virtua\FreshMail\Api\SubscriberListServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;

class SubscriberListService implements SubscriberListServiceInterface
{
    /** @var FreshMailApiInterfaceFactory */
    private $freshMailApiFactory;
    /** @var FreshMailApiInterface|null */
    private $freshMailApi;
    /** @var Logger */
    protected $logger;
    /** @var GetBearerTokenForListHash */
    private $getBearerTokenForListHash;

    public function __construct(
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        Logger $logger,
        GetBearerTokenForListHash $getBearerTokenForListHash
    ) {
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->logger = $logger;
        $this->getBearerTokenForListHash = $getBearerTokenForListHash;
    }

    public function getLists(?string $token = null): array
    {
        return $this->getFreshMailApi($token)->getLists();
    }

    public function hashListExists(string $hashList): bool
    {
        $token = $this->getBearerTokenForListHash->execute($hashList);
        $lists = $this->getLists($token ?: null);
        foreach ($lists as $list) {
            if ($list['subscriberListHash'] === $hashList) {
                return true;
            }
        }

        return false;
    }

    private function getFreshMailApi(?string $token = null): FreshMailApiInterface
    {
        if (! $this->freshMailApi) {
            $this->freshMailApi = $this->freshMailApiFactory->create($token);
        }

        return $this->freshMailApi;
    }
}
