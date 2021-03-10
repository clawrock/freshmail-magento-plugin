<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\Email\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Virtua\FreshMail\Model\Cron\ScheduleFreshMailTemplates;

class GetTemplatesFromFreshMail extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Virtua_FreshMail::config_freshmail';

    /**
     * @var ScheduleFreshMailTemplates
     */
    protected $cronSchedule;

    public function __construct(
        Context $context,
        ScheduleFreshMailTemplates $cronSchedule
    ) {
        parent::__construct($context);
        $this->cronSchedule = $cronSchedule;
    }

    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        try {
            $this->cronSchedule->scheduleGetTemplatesJob();
            $this->messageManager->addSuccessMessage(__('Synchronize job added to queue. The templates will be synchronized within 2 minutes.'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $resultRedirect;
    }
}
