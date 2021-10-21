<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Psr\Log\LoggerInterface;

class UpdateSubscriptionEntryAfterCustomerSave
{
    /** @var SubscriberFactory */
    private $subscriberFactory;
    /** @var Subscriber */
    private $subscriberResource;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        Subscriber $subscriberResource,
        LoggerInterface $logger
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->subscriberResource = $subscriberResource;
        $this->logger = $logger;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(CustomerRepositoryInterface $subject, CustomerInterface $result): CustomerInterface
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadBySubscriberEmail($result->getEmail(), (int) $result->getWebsiteId());
        if ($subscriber->getId() && !$subscriber->getCustomerId()) {
            $subscriber->setCustomerId((int) $result->getId());
            try {
                $this->subscriberResource->save($subscriber);
            } catch (AlreadyExistsException $e) {
                return $result;
            } catch (\Exception $e) {
                $this->logger->error('Unable to update subscription entry!', ['exception' => $e]);
            }
        }

        return $result;
    }
}
