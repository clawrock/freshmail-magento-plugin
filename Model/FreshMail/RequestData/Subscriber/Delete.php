<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Assert\Assert;
use Virtua\FreshMail\Api\RequestData\Subscriber\DeleteInterface;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;

class Delete extends AbstractRequestData implements DeleteInterface
{
    public function __construct(
        string $email,
        string $list
    ) {
        $this->setEmail($email);
        $this->setList($list);
    }

    public function setEmail(string $email): void
    {
        $message = 'Email should be valid string (' . $email . ')';
        Assert::that($email, $message)->email();
        $this->data['email'] = $email;
    }

    public function getEmail(): string
    {
        return $this->data['email'];
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
