<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\Widget\Button;

use Magento\Backend\Block\Widget\Button;

class Action extends Button
{
    public function getClass(): string
    {
        return 'action-primary';
    }
}
