<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Magento\Framework\ObjectManagerInterface;
use Virtua\FreshMail\Api\IntegrationServiceInterfaceFactory;
use Virtua\FreshMail\Api\IntegrationServiceInterface;

class IntegrationServiceFactory implements IntegrationServiceInterfaceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;


    public function __construct(
        ObjectManagerInterface $objectManager,
        FreshMailApiInterfaceFactory $freshMailApiFactory
        
    ) {
        $this->objectManager = $objectManager;
        $this->freshMailApiFactory = $freshMailApiFactory;
    }

    public function create(?FreshMailApiInterface $freshMailApi = null): IntegrationServiceInterface
    {
        return $this->objectManager->create(IntegrationServiceInterface::class, [
            'freshMailApi' => $freshMailApi ?: $this->freshMailApiFactory->create()
        ]);
    }
}