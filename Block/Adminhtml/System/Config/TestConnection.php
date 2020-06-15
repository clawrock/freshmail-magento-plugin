<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    protected function _prepareLayout(): self
    {
        parent::_prepareLayout();
        $this->setTemplate('Virtua_FreshMail::system/config/testconnection.phtml');

        return $this;
    }

    /**
     * Unset some non-related element parameters
     */
    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->setScope(null);
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('freshmail/system_config/testConnection'),
                'field_mapping' => str_replace('"', '\\"', json_encode($this->_getFieldMapping())),
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Returns configuration fields required to perform the ping request
     *
     * @return string[]
     */
    protected function _getFieldMapping(): array
    {
        return [
            'bearer_token' => 'freshmail_connection_bearer_token',
        ];
    }
}
