<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\System\Config\Form\Field\Lists\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Virtua\FreshMail\Model\Config\Source\Lists\ListDataFactory;

class ListData implements OptionSourceInterface
{
    /**
     * @var ListDataFactory
     */
    protected $listDataFactory;

    public function __construct(
        ListDataFactory $listDataFactory
    ) {
        $this->listDataFactory = $listDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        try {
            $listData = $this->listDataFactory->create();
            $options = $listData->toOptionArray();
        } catch (\Throwable $e) {
            return  [[
                'value' => '',
                'label' => __('-- Please Select --'),
            ]];
        }

        return $options;
    }
}
