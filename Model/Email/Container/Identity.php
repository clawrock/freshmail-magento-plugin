<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Email\Container;

class Identity
{
    /**
     * @var string
     */
    protected $customerName;

    /**
     * @var string
     */
    protected $customerEmail;

    /**
     * @var int
     */
    protected $storeId;

    public function setCustomerName(string $name): void
    {
        $this->customerName = $name;
    }

    public function setCustomerEmail(string $email): void
    {
        $this->customerEmail = $email;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function getStoreId(): ?int
    {
        return $this->storeId;
    }
}
