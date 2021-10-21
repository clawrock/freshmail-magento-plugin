<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

trait GetCustomersCollectionTrait
{
    /** @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory  */
    private $customerCollectionFactory;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    public function getCustomerCollection(): \Magento\Customer\Model\ResourceModel\Customer\Collection
    {
        return $this->customerCollectionFactory->create();
    }
}
