<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Plugin;

use Virtua\FreshMail\Api\RequestDataInterface;

class AddSubscriberCustomFields
{
    /** @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory */
    private $customerCollectionFactory;
    /** @var \Magento\Customer\Api\Data\CustomerInterface[] */
    private $customers;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @param RequestDataInterface $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDataArray(
        RequestDataInterface $subject,
        array $result
    ): array {
        if (!empty($result['email'])) {
            $result = $this->updateCustomFields($result);
        }
        if (!empty($result['subscribers'])) {
            foreach ($result['subscribers'] as $key => $subscriber) {
                $result['subscribers'][$key] = $this->updateCustomFields($subscriber);
            }
        }

        return $result;
    }

    private function updateCustomFields(array $subscriber): array
    {
        $customers = $this->getCustomers();
        if (!empty($subscriber['email']) && isset($customers[$subscriber['email']])) {
            $subscriber['custom_fields'] = [
                'imie' => $customers[$subscriber['email']]->getFirstname(),
                'nazwisko' => $customers[$subscriber['email']]->getLastname()
            ];
        }

        return $subscriber;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]
     */
    private function getCustomers(): array
    {
        if ($this->customers !== null) {
            return $this->customers;
        }
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customers */
        $customers = $this->customerCollectionFactory->create();
        $customers->getSelect()->join(
            ['ns' => $customers->getTable('newsletter_subscriber')],
            'ns.subscriber_email = e.email OR ns.customer_id = e.entity_id',
            []
        )->group('e.email');
        $this->customers = [];
        /** @var \Magento\Customer\Model\Customer $customer */
        foreach ($customers as $customer) {
            $this->customers[$customer->getEmail()] = $customer->getDataModel();
        }

        return $this->customers;
    }
}
