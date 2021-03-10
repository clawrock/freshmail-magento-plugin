<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Block\Email\AbandonedCart\Items\Renderer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Helper\Image as ImageHelper;

class DefaultRenderer extends Template
{
    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    public function __construct(
        PriceHelper $priceHelper,
        ImageHelper $imageHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceHelper = $priceHelper;
        $this->imageHelper = $imageHelper;
    }

    protected $placeholdersMap = [
        'product_name' => 'name',
        'product_quantity' => 'qty'
    ];

    protected $placeholdersPriceMap = [
        'unit_price_tax_incl' => 'price_incl_tax'
    ];

    protected $placeholdersImageMap = [
        'product_cover' => 'image'
    ];

    public function getProductHtml(): string
    {
        return $this->changePlaceholdersWithRealData($this->getItemHtmlTemplate() ?? '');
    }

    protected function changePlaceholdersWithRealData(string $itemHtmlTemplate): string
    {
        foreach ($this->placeholdersMap as $variable => $key) {
            $itemHtmlTemplate = preg_replace(
                '/{' . $variable . '}/',
                $this->getItem()->getData($key),
                $itemHtmlTemplate
            );
        }

        foreach ($this->placeholdersPriceMap as $variable => $key) {
            $formattedPrice = $this->priceHelper->currency(
                (float) $this->getItem()->getData($key),
                true,
                false
            );
            $itemHtmlTemplate = preg_replace(
                '/{' . $variable . '}/',
                $this->escapeCurrency($formattedPrice),
                $itemHtmlTemplate
            );
        }

        foreach ($this->placeholdersImageMap as $variable => $key) {
            $itemHtmlTemplate = preg_replace(
                '/{' . $variable . '}/',
                $this->imageHelper->init($this->getItem()->getProduct(), 'thumbnail', ['type'=>'thumbnail'])->getUrl(),
                $itemHtmlTemplate
            );
        }

        return $itemHtmlTemplate;
    }

    private function escapeCurrency(string $price): string
    {
        if (strpos($price, '$') !== false) {
            $price = substr_replace($price, "\\", strpos($price, '$'), 0);
        }

        return $price;
    }
}
