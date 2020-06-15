<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\System\Config\Form\Field\Lists\Frontend;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button as WidgetButton;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer;

class RefreshButton extends Field
{
    /**
     * @var Serializer\Json
     */
    protected $serializer;

    public function __construct(
        Template\Context $context,
        Serializer\Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
    }

    /**
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $html = $element->setStyle('width:200px')->getElementHtml();
        $html .= $this->getButtonHtml();

        return $html;
    }

    /**
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
    {
        /** @var WidgetButton $button */
        $button = $this->getLayout()->createBlock(WidgetButton::class);
        $button->setData([
            'id' => 'freshmail_lists_refresh',
            'label' => __('Refresh'),
        ]);

        $dataMageInit = $this->escapeHtml(
            $this->serializer->serialize(
                [
                    'freshMailRefreshSubscriptionList' => [
                        'url' => $this->_urlBuilder->getUrl($this->getRefreshUrl()),
                        'elementId' => $button->getHtmlId(),
                        'selectId' => 'freshmail_lists_list',
                    ],
                ]
            )
        ) . '"';

        $button->setData('data-mage-init', $dataMageInit);
        $button->setTemplate('Virtua_FreshMail::system/config/subscriberList/refreshButton.phtml');

        return $button->toHtml();
    }

    protected function getRefreshUrl(): string
    {
        return $this->getUrl('freshmail/system_config/refreshLists');
    }
}
