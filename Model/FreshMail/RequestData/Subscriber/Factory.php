<?php
// todo check if this is needed
declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Subscriber;

use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var string
     */
    protected $instanceName = null;

    public function __construct(
        ObjectManagerInterface $objectManager,
        string $instanceName
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = []): AbstractRequestData
    {
        return $this->objectManager->create($this->instanceName, $data);
    }

    public function createFromJson(string $jsonData): AbstractRequestData
    {
        return $this->create(json_decode($jsonData));
    }
}