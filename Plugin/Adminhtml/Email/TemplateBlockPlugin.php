<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Plugin\Adminhtml\Email;

use Magento\Backend\Model\UrlInterface;
use Magento\Email\Block\Adminhtml\Template;
use Virtua\FreshMail\Block\Adminhtml\Widget\Button\Action as WidgetButton;
use Virtua\FreshMail\Model\System\Config;

class TemplateBlockPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    public function beforeSetLayout(Template $subject): void
    {
        if (! $this->config->isEnabled()) {
            return;
        }

        $addButtonProps = [
            'id' => 'freshmail_action_get_templates',
            'label' => __('Synchronize templates with FreshMail'),
            'onclick' => "setLocation('" . $this->getFreshMailTemplatesActionUrl() . "')",
            'class_name' => WidgetButton::class,
        ];

        $subject->addButton('freshmail_action_get_templates', $addButtonProps);
    }

    protected function getFreshMailTemplatesActionUrl(): string
    {
        return $this->urlBuilder->getUrl('freshmail/email_template/getTemplatesFromFreshMail');
    }
}
