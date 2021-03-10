<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class EditTemplate extends Field
{
    protected function _prepareLayout(): self
    {
        parent::_prepareLayout();
        $this->setTemplate('Virtua_FreshMail::system/config/edit_template.phtml');

        return $this;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('freshmail/system_config/getEditTemplateUrl')
            ]
        );

        return $this->_toHtml();
    }
}
