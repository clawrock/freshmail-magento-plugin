<?php
// todo instead of returnin array return some response object
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use \Exception;
use Magento\Framework\Exception\LocalizedException;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Model\FreshMail\ApiV2Factory;
use Virtua\FreshMail\Model\FreshMail\ApiV2 as ApiV2Client;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Api\SubscriberServiceInterface;
use Virtua\FreshMail\Api\RequestData;
use Virtua\FreshMail\Api\ResponseData\ResponseInterface;
use Virtua\FreshMail\Api\ResponseData\ResponseInterfaceFactory;

class FreshMailApi implements FreshMailApiInterface
{
    private const API_GET_LISTS_RESPONSE_ARRAY_KEY = 'lists';

    /**
     * @var ApiV2Client
     */
    private $apiV2;

    /**
     * @var ApiV2Factory
     */
    private $apiV2Factory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SubscriberServiceInterface
     */
    private $subscriberService;

    /**
     * @var ResponseInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $bearerToken;

    public function __construct(
        ApiV2Factory $apiV2Factory,
        Config $config,
        Logger $logger,
        SubscriberServiceInterface $subscriberService,
        ResponseInterfaceFactory $responseFactory,
        string $bearerToken = ''
    ) {
        $this->apiV2Factory = $apiV2Factory;
        $this->config = $config;
        $this->logger = $logger;
        $this->subscriberService = $subscriberService;
        $this->responseFactory = $responseFactory;
        $this->bearerToken = $bearerToken;
    }

    public function testConnection(): bool
    {
        $response = $this->getApiV2()->doRequest(self::API_TEST_CONNECTION);

        return $response['data'] === 'pong' ? true : false;
    }

    public function getLists(): array
    {
        $lists = [];
        try {
            $response = $this->getApiV2()->doRequest(self::API_GET_LISTS);
            $lists = $response[self::API_GET_LISTS_RESPONSE_ARRAY_KEY] ?? [];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $lists;
    }

    /**
     * @throws Exception
     */
    public function addSubscriber(RequestData\Subscriber\AddInterface $subscriberAdd): void
    {
        $this->getApiV2()->doRequest(self::API_ADD_SUBSCRIBER, $subscriberAdd->getDataArray());
    }

    /**
     * @throws Exception
     */
    public function editSubscriber(RequestData\Subscriber\EditInterface $subscriberEdit): void
    {
        $this->getApiV2()->doRequest(self::API_EDIT_SUBSCRIBER, $subscriberEdit->getDataArray());
    }

    /**
     * @throws Exception
     */
    public function getSubscriber(RequestData\Subscriber\GetInterface $subscriberGet): ResponseInterface
    {
        $requestUri = self::API_GET_SUBSCRIBER . '/' . $subscriberGet->getList() . '/' . $subscriberGet->getEmail();
        $response = $this->getApiV2()->doRequest($requestUri);

        return $this->responseFactory->create(['response' => $response]);
    }

    /**
     * @throws Exception
     */
    public function addMultipleSubscribers(RequestData\Subscriber\AddMultipleInterface $subscriberAddMultiple): void
    {
        $this->getApiV2()->doRequest(self::API_ADD_MULTIPLE_SUBSCRIBERS, $subscriberAddMultiple->getDataArray());
    }

    /**
     * @throws Exception
     */
    public function editMultipleSubscribers(RequestData\Subscriber\EditMultipleInterface $editMultiple): void
    {
        $this->getApiV2()->doRequest(self::API_EDIT_MULTIPLE_SUBSCRIBERS, $editMultiple->getDataArray());
    }

    /**
     * @throws Exception
     */
    public function getMultipleSubscribers(
        RequestData\Subscriber\GetMultipleInterface $subscriberGetMultiple
    ): ResponseInterface {
        $response = $this->getApiV2()->doRequest(
            self::API_GET_MULTIPLE_SUBSCRIBERS,
            $subscriberGetMultiple->getDataArray());

        return $this->responseFactory->create(['response' => $response]);
    }

    /**
     * @throws Exception
     */
    public function deleteSubscriber(RequestData\Subscriber\DeleteInterface $subscriberDelete): void
    {
        $this->getApiV2()->doRequest(self::API_DELETE_SUBSCRIBER, $subscriberDelete->getDataArray());
    }

    private function getApiV2(): ApiV2Client
    {
        if (! $this->apiV2) {
            $this->apiV2 = $this->apiV2Factory->create($this->bearerToken);
        }
        return $this->apiV2;
    }
}