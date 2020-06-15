<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\Newsletter\Subscriber\Grid;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Virtua\FreshMail\Block\Adminhtml\Widget\Button\Action as WidgetButton;
use Virtua\FreshMail\Model\System\Config;

class SyncButton extends Container
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    protected function _prepareLayout(): Container
    {
        if ($this->config->isEnabled()) {
            $this->initSyncButton();
        }

        return parent::_prepareLayout();
    }

    protected function initSyncButton(): void
    {
        $addButtonProps = [
            'id' => 'freshmail_action_sync_subscribers',
            'label' => __('Freshmail Synchronization'),
            'onclick' => "setLocation('" . $this->getSyncButtonActionLink() . "')",
            'class_name' => WidgetButton::class,
        ];

        $this->buttonList->add('freshmail_sync_button', $addButtonProps);
    }

    protected function getSyncButtonActionLink(): string
    {
        return $this->getUrl('freshmail/newsletter_subscriber/syncButton');
    }
}
