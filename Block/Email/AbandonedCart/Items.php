<?php
// todo check if this class is ok, maybe some refactor
declare(strict_types=1);

namespace Virtua\FreshMail\Block\Email\AbandonedCart;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use \RuntimeException;

class Items extends Template
{
    const DEFAULT_TYPE = 'default';

    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getItemHtml(DataObject $item): string
    {
        $type = $this->getItemType($item);
        $block = $this->getItemRenderer($type)
            ->setItem($item)
            ->setItemHtmlTemplate($this->getItemHtmlTemplate());  //todo maybe think of some prettier way to do this

        return $block->toHtml();
    }

    private function getItemType(DataObject $item): string
    {
        return $item->getProductType();
    }

    /**
     * @throws RuntimeException
     */
    public function getItemRenderer(string $type): AbstractBlock
    {
        /** @var \Magento\Framework\View\Element\RendererList $rendererList */
        $rendererList = $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        $renderer = $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
        $renderer->setRenderedBlock($this);
        return $renderer;
    }
}
