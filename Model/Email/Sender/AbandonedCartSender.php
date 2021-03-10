<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Email\Sender;

use \Exception;
use Virtua\FreshMail\Model\Email\Sender;
use Virtua\FreshMail\Api\Email\SenderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\DataObject;


class AbandonedCartSender extends Sender implements SenderInterface
{
    /**
     * @var CartInterface|null
     */
    private $quote;

    /**
     * @var string|null
     */
    private $itemHtmlTemplate;

    public function send(): void
    {
        try {
            $this->checkAndSend();
        } catch (Exception $e){
            $a = $e->getMessage();
            //todo handle exception
        }
    }


    protected function prepareTemplate(): void
    {
        $transport = [
            'quote' => $this->quote,
            'quoteItems' => $this->quote->getItems(),
            'store' => $this->quote->getStore(),
            'itemHtmlTemplate' => $this->itemHtmlTemplate
        ];

        $transportObject = new DataObject($transport);
        $this->templateContainer->setTemplateVars($transportObject->getData());

        parent::prepareTemplate();
    }


    public function setQuote(CartInterface $quote): void
    {
        $this->quote = $quote;
    }

    public function setItemHtmlTemplate(string $itemHtmlTemplate): void
    {
        $this->itemHtmlTemplate = $itemHtmlTemplate;
    }
}
