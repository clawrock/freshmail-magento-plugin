<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\Newsletter\Subscriber;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;

class SyncButton extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Virtua_FreshMail::config_freshmail';

    /**
     * @var RequestQueueServiceInterface
     */
    private $requestQueueService;

    public function __construct(
        Context $context,
        RequestQueueServiceInterface $requestQueueService
    ) {
        parent::__construct($context);
        $this->requestQueueService = $requestQueueService;
    }

    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        try {
            $this->requestQueueService->addFullSyncToQueue();
            $this->messageManager->addSuccessMessage('FreshMail synchronization has been added to queue.');
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
