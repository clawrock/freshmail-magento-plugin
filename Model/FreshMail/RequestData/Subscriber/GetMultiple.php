<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Assert\Assert;
use LogicException;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;
use Virtua\FreshMail\Api\RequestData\Subscriber\GetMultipleInterface;

class GetMultiple extends AbstractRequestData implements GetMultipleInterface
{
    /**
     * @var int
     */
    private $count = 0;

    public function __construct(
        string $list,
        ?array $subscribers = []
    ) {
        if(! empty($subscribers)) {
            $this->setSubscribers($subscribers);
        }

        $this->setList($list);
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

    public function getSubscribers(): array
    {
        return $this->data['subscribers'];
    }

    /**
     * @throws LogicException
     */
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

    public function getCount(): int
    {
        return $this->count;
    }
}
