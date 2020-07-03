<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Banner extends Fieldset
{
    private const BANNER_URL = 'https://freshmail.pl/maile-transakcyjne/ecommerce/';

    public function render(AbstractElement $element): string
    {
        return <<<EOT
<a href="{$this->getFreshMailBannerLink()}">
    <img style="margin-bottom: 20px" src="{$this->getBannerImage()}" alt="FreshMail Transactional Banner">
</a>
EOT;
    }

    private function getFreshMailBannerLink(): string
    {
        return self::BANNER_URL;
    }

    private function getBannerImage(): string
    {
        return $this->getViewFileUrl('Virtua_FreshMail::images/freshmail.png');
    }
}
