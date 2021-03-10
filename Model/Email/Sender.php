<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Email;

use \Exception;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Model\Email\Container\Identity;
use Virtua\FreshMail\Model\Email\Container\Template;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\Email\SenderBuilderFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Virtua\FreshMail\Api\FollowUpEmailRepositoryInterface;

abstract class Sender
{
    /**
     * @var FollowUpEmailInterface|null
     */
    protected $followUpEmail;

    /**
     * @var SenderBuilderFactory
     */
    protected $senderBuilderFactory;

    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var Identity
     */
    protected $identityContainer;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var FollowUpEmailRepositoryInterface
     */
    protected $followUpEmailRepository;

    public function __construct(
        Template $templateContainer,
        Identity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        Logger $logger,
        FollowUpEmailInterface $followUpEmail,
        CustomerRepositoryInterface $customerRepository,
        FollowUpEmailRepositoryInterface $followUpEmailRepository
    ) {
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->logger = $logger;
        $this->followUpEmail = $followUpEmail;
        $this->customerRepository = $customerRepository;
        $this->followUpEmailRepository = $followUpEmailRepository;
    }

    protected function checkAndSend(): void
    {
        $this->prepareTemplate();

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        try {
            $sender->send();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    protected function prepareTemplate(): void
    {
        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        $this->identityContainer->setCustomerName($this->getCustomerName());
        $this->identityContainer->setCustomerEmail($this->followUpEmail->getCustomerEmail());
        $this->identityContainer->setStoreId($this->followUpEmail->getStoreId());
        $this->templateContainer->setTemplateId($this->followUpEmail->getTemplateId());
    }

    private function getCustomerName(): ?string // todo think about moving it, maybe storing customer name in follow up email table
    {
        try {
            $customer = $this->customerRepository->getById($this->followUpEmail->getCustomerId());
        } catch (Exception $e) {
            return null;
        }

        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }

    protected function getSender(): SenderBuilder
    {
        return $this->senderBuilderFactory->create(
            [
                'templateContainer' => $this->templateContainer,
                'identityContainer' => $this->identityContainer,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getTemplateOptions()
    {
        return [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->followUpEmail->getStoreId()
        ];
    }
}
