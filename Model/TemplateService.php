<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use \Exception;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\RequestData;
use Virtua\FreshMail\Api\ResponseData;
use Virtua\FreshMail\Api\TemplateServiceInterface;
use Virtua\FreshMail\Logger\Logger;

class TemplateService implements TemplateServiceInterface
{
    private const TEMPLATE_LIST_LIMIT = 500;
    private const EDIT_TEMPLATE_URL = 'https://app.freshmail.com/pl/designer/newedit/?template_hash=';
    private const DEFAULT_ABANDONED_CART_TEMPLATE_ID = 'df8l7dj1f7'; // todo change to correct ID and think if it is needed

    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var RequestData\Templates\TemplateInterfaceFactory
     */
    private $requestTemplateFactory;

    /**
     * @var RequestData\Templates\ListsInterfaceFactory
     */
    private $requestListsFactory;

    /**
     * @var ResponseData\Templates\TemplateInterfaceFactory
     */
    private $responseTemplateFactory;

    /**
     * @var FreshMailApiInterface
     */
    private $freshMailApi;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        RequestData\Templates\TemplateInterfaceFactory $requestTemplateFactory,
        RequestData\Templates\ListsInterfaceFactory $requestListsFactory,
        ResponseData\Templates\TemplateInterfaceFactory $responseTemplateFactory,
        Logger $logger
    ) {
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->requestTemplateFactory = $requestTemplateFactory;
        $this->requestListsFactory = $requestListsFactory;
        $this->responseTemplateFactory = $responseTemplateFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getTemplatesFromFreshMail(): array
    {
        $requestData = $this->requestListsFactory->create([
            'offset' => 0,
            'limit' => self::TEMPLATE_LIST_LIMIT
        ]);

        $response = $this->getFreshMailApi()->getTemplateList($requestData);
        $list = $response->getData();
        $i = 1;
        while (count($response->getData()) >= self::TEMPLATE_LIST_LIMIT) {
            $requestData = $this->requestListsFactory->create([
                'offset' => $i * self::TEMPLATE_LIST_LIMIT,
                'limit' => self::TEMPLATE_LIST_LIMIT
            ]);
            $response = $this->getFreshMailApi()->getTemplateList($requestData);
            $list = array_merge($list, $response->getData());
            $i++;
        }
        return $this->createTemplateInstancesFromDataArray($list);
    }

    /**
     * @return ResponseData\Templates\TemplateInterface[]
     * @throws Exception
     */
    private function createTemplateInstancesFromDataArray(array $list): array
    {
        $templates = [];
        foreach ($list as $templateData) {
            $templates[] = $this->getTemplateByHash($templateData['id_hash']);
        }

        return $templates;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateByHash(string $hashId): ResponseData\Templates\TemplateInterface
    {
        $requestTemplate = $this->requestTemplateFactory->create([
            'hash' => $hashId
        ]);

        return $this->responseTemplateFactory->create(
            $this->getFreshMailApi()->getTemplate($requestTemplate)->getData()
        );
    }

    public function transformFreshMailTemplateToMagentoFormat(ResponseData\Templates\TemplateInterface $template): string
    {
        $text = $template->getContent();
        $text = preg_replace(
            '/{products_list}/',
            '{{layout handle="freshmail_email_abandonedcart_cart_items" quoteItems=$quoteItems itemHtmlTemplate=$itemHtmlTemplate area="frontend"}}',
            $text
        );

        return $text;
    }

    public function transformFreshMailTemplateAdditionalTextToMagentoFormat(ResponseData\Templates\TemplateInterface $template): string
    {
        $text = '';
        if ($template->getProductContent()) {
            $text = $this->transformProductHtml($template->getProductContent());
        }

        return $text;
    }

    private function transformProductHtml(string $productHtml): string
    {
        return $productHtml;
    }

    public function getFreshMailTemplateEditLinkByHashId(string $hash): string
    {
        return self::EDIT_TEMPLATE_URL . $hash;
    }

    private function getFreshMailApi(): FreshMailApiInterface
    {
        if (! $this->freshMailApi) {
            $this->freshMailApi = $this->freshMailApiFactory->create();
        }

        return $this->freshMailApi;
    }
}
