<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Virtua\FreshMail\Api\TemplateRepositoryInterface;

class FreshMail extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        return $row->getData(TemplateRepositoryInterface::FRESHMAIL_TEMPLATE_ID_HASH) ? __('Yes') : __('No');
    }
}
