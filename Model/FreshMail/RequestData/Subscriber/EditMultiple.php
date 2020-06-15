<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Assert\Assert;
use LogicException;
use Virtua\FreshMail\Model\FreshMail\StatusService;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;
use Virtua\FreshMail\Api\RequestData\Subscriber\EditMultipleInterface;

class EditMultiple extends AbstractRequestData implements EditMultipleInterface
{
    /**
     * @var int
     */
    private $count = 0;

    public function __construct(
        string $list,
        ?array $subscribers = [],
        ?int $state = null
    ) {
        if(! empty($subscribers)) {
            $this->setSubscribers($subscribers);
        }

        $this->setList($list);
        $this->setState($state);
    }

    /**
     * @throws LogicException
     */
    public function addSubscriber(string $email): void
    {
        if (FreshMailApiInterface::API_REQUEST_LIMIT === $this->count) {
            throw new LogicException(
                'API limit allows to send up to ' . FreshMailApiInterface::API_REQUEST_LIMIT . ' subscribers.'
            );
        }

        $this->data['subscribers'][] = [
            'email' => $email,
        ];

        $this->count++;
    }

    public function getSubscribers(): array
    {
        return $this->data['subscribers'];
    }

    public function setSubscribers(array $data): void
    {
        $countData = count($data);
        if ($countData > FreshMailApiInterface::API_REQUEST_LIMIT) {
            throw new LogicException(
                'API limit allows to send up to ' . FreshMailApiInterface::API_REQUEST_LIMIT . ' subscribers.'
            );
        }

        $this->data['subscribers'] = $data;
        $this->count = $countData;
    }

    public function setState(?int $state = null): void
    {
        if (null !== $state) {
            $message = 'Invalid value (' . $state . ') in state param';
            Assert::that($state, $message)->choice(StatusService::allFreshMailSubscriberStatuses());
            $this->data['state'] = $state;
        }
    }

    public function getState(): ?int
    {
        return $this->data['state'];
    }

    public function setList(string $list): void
    {
        $message = 'List hash cannot be a blank string';
        Assert::that($list, $message)->notBlank()->string();
        $this->data['list'] = $list;
    }

    public function getList(): string
    {
        return $this->data['list'];
    }
}
