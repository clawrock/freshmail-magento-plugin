<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Assert\Assert;
use LogicException;
use Virtua\FreshMail\Model\FreshMail\StatusService;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;
use Virtua\FreshMail\Api\RequestData\Subscriber\AddMultipleInterface;

class AddMultiple extends AbstractRequestData implements AddMultipleInterface
{
    /**
     * @var int
     */
    private $count = 0;

    public function __construct(
        string $list,
        int $confirm = 0,
        ?array $subscribers = [],
        ?int $state = null
    ) {
        if(! empty($subscribers)) {
            $this->setSubscribers($subscribers);
        }

        $this->setList($list);
        $this->setState($state);
        $this->setConfirm($confirm);
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

        $message = __('Email should be valid string (%email)', ['email' => $email]);
        Assert::that($email, $message)->email();

        $this->data['subscribers'][] = [
            'email' => $email,
        ];

        $this->count++;
    }

    public function setSubscribers(array $subscribers): void
    {
        $countData = count($subscribers);
        if ($countData > FreshMailApiInterface::API_REQUEST_LIMIT) {
            throw new LogicException(
                'API limit allows to send up to ' . FreshMailApiInterface::API_REQUEST_LIMIT . ' subscribers.'
            );
        }

        $this->data['subscribers'] = $subscribers;
        $this->count = $countData;
    }

    public function getSubscribers(): array
    {
        return $this->data['subscribers'];
    }

    public function setState(?int $state = null): void
    {
        if (null !== $state) {
            $message = __('Invalid value (%state) in state param', ['state' => $state]);
            Assert::that($state, $message)->choice(StatusService::allFreshMailSubscriberStatuses());
            $this->data['state'] = $state;
        }
    }

    public function getState(): int
    {
        return $this->data['state'];
    }

    public function setList(string $list): void
    {
        $message = __('List hash cannot be a blank string');
        Assert::that($list, $message)->notBlank()->string();
        $this->data['list'] = $list;
    }

    public function getList(): string
    {
        return $this->data['list'];
    }

    public function setConfirm(int $confirm = 0): void
    {
        $message = __('Invalid value (%confirm) in confirm param', ['confirm' => $confirm]);
        Assert::that($confirm, $message)->choice([0, 1]);
        $this->data['confirm'] = $confirm;
    }

    public function getConfirm(): int
    {
        return $this->data['confirm'];
    }
}
