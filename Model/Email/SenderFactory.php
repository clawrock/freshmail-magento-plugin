<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Email;

use Virtua\FreshMail\Api\Email\SenderFactoryInterface;
use Virtua\FreshMail\Api\Email\SenderInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Magento\Framework\ObjectManagerInterface;
use Virtua\FreshMail\Model\Email\Sender\AbandonedCartSender;

class SenderFactory implements SenderFactoryInterface
{
    private $typeMapping = [
        FollowUpEmailInterface::TYPE_ABANDONED_FIRST => AbandonedCartSender::class,
        FollowUpEmailInterface::TYPE_ABANDONED_SECOND => AbandonedCartSender::class,
        FollowUpEmailInterface::TYPE_ABANDONED_THIRD => AbandonedCartSender::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;

    }

    public function create(FollowUpEmailInterface $followUpEmail): SenderInterface
    {
        $senderClass = $this->typeMapping[$followUpEmail->getType()];
        return $this->objectManager->create($senderClass, [
            'followUpEmail' => $followUpEmail
        ]);
    }
}
