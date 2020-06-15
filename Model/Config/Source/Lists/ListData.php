<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\Config\Source\Lists;

use Magento\Framework\Exception\LocalizedException;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Exception\ApiException;
use Virtua\FreshMail\Api\SubscriberListServiceInterface;

class ListData
{
    /**
     * @var SubscriberListServiceInterface
     */
    protected $subscriberListService;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config,
        SubscriberListServiceInterface $subscriberListService
    ) {
        $this->config = $config;
        $this->subscriberListService = $subscriberListService;
    }

    /**
     * @throws ApiException
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $options = [];

        $default = [[
            'value' => '',
            'label' => __('-- Please Select --'),
        ]];

        foreach ($this->subscriberListService->getLists() as $list) {
            $options[] = [
                'value' => $list['subscriberListHash'],
                'label' => $list['name'],
            ];
        }

        return array_merge($default, $options);
    }
}
