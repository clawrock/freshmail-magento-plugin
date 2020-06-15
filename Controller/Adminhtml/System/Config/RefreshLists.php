<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result;
use Virtua\FreshMail\Model\Config\Source\Lists\ListDataFactory;

class RefreshLists extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Virtua_FreshMail::config_freshmail';

    /**
     * @var Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ListDataFactory
     */
    protected $listDataFactory;

    public function __construct(
        Context $context,
        Result\JsonFactory $resultJsonFactory,
        ListDataFactory $listDataFactory
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->listDataFactory = $listDataFactory;
    }

    public function execute(): Result\Json
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        try {
            $listData = $this->listDataFactory->create();
            $options = $listData->toOptionArray();
            $result['success'] = true;
            $result['options'] = $options;
        } catch (\Throwable $e) {
            $result['errorMessage'] = __($e->getMessage());
        }

        /** @var Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }
}
