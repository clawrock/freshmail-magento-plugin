<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;
use Virtua\FreshMail\Model\ResourceModel\RequestQueue as RequestQueueResource;

class RequestQueue extends AbstractModel implements RequestQueueInterface, IdentityInterface
{
    /**
     * @var array|null
     */
    protected static $statuses = null;

    /**
     * @var array|null
     */
    protected static $actions = null;

    /**
     * @var Serializer\Json
     */
    protected $serializer;

    protected function _construct(): void
    {
        $this->_init(RequestQueueResource::class);
    }

    public function __construct(
        Context $context,
        Registry $registry,
        Serializer\Json $serializer,
        ?ResourceModel\AbstractResource $resource = null,
        ?Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $tags = [];
        if ($this->getEntityId()) {
            $tags[] = self::CACHE_TAG . '_' . $this->getEntityId();
        }

        return $tags;
    }

    public static function getStatuses(): array
    {
        if (null === self::$statuses) {
            self::$statuses = [
                self::STATUS_PENDING => __('Pending'),
                self::STATUS_SUCCESS => __('Success'),
                self::STATUS_ERROR => __('Error'),
            ];
        }

        return self::$statuses;
    }

    public static function getStatusName(int $statusId): Phrase
    {
        if (null === self::$statuses) {
            self::getStatuses();
        }

        if (isset(self::$statuses[$statusId])) {
            return self::$statuses[$statusId];
        }

        return __('Unknown Status');
    }

    public static function geActions(): array
    {
        if (null === self::$actions) {
            self::$actions = [
                self::ACTION_ADD_USER => __('Add a new subscriber'),
                self::ACTION_EDIT_USER => __('Edit existing subscriber'),
                self::ACTION_RESIGN_USER => __('Unsubscribe user'),
                self::ACTION_DELETE_USER => __('Delete subscriber'),
                self::ACTION_FULL_SYNC_EMAILS => __('Full Sync Emails'),
            ];
        }

        return self::$actions;
    }

    public static function getActionName(int $actionId): Phrase
    {
        if (null === self::$actions) {
            self::geActions();
        }

        if (isset(self::$actions[$actionId])) {
            return self::$actions[$actionId];
        }

        return __('Unknown Action');
    }

    public function getId(): ?int
    {
        $id = $this->getData(self::ID);
        if (null !== $id) {
            $id = (int) $id;
        }

        return $id;
    }

    public function getAction(): int
    {
        return (int) $this->getData(self::ACTION);
    }

    public function setActon(int $value): void
    {
        $this->setData(self::ACTION, $value);
    }

    public function getParamsArray(): array
    {
        $data = $this->getData(self::PARAMS);
        if (is_string($data)) {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
    }

    public function setParamsArray(array $params): void
    {
        $data = $this->serializer->serialize($params);
        $this->setData(self::PARAMS, $data);
    }

    public function getParams(): string
    {
        return $this->getData(self::PARAMS);
    }

    public function setParams(string $params): void
    {
        $this->setData(self::PARAMS, $data);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getProcessedAt(): string
    {
        return $this->getData(self::PROCESSED_AT);
    }

    public function setProcessedAt(string $processedAt): void
    {
        $this->setData(self::PROCESSED_AT, $processedAt);
    }

    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    public function setStatus(int $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    public function getErrors(): string
    {
        return $this->getData(self::ERRORS);
    }

    public function setErrors(string $errors): void
    {
        $this->setData(self::ERRORS, $errors);
    }
}
