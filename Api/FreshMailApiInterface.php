<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use FreshMail\Api\Client\Exception\ClientException;
use FreshMail\Api\Client\Exception\RequestException;
use FreshMail\Api\Client\Response\HttpResponse;
use Virtua\FreshMail\Api\RequestData;
use Virtua\FreshMail\Api\ResponseData\ResponseInterface;
use Virtua\FreshMail\Exception\ApiException;

interface FreshMailApiInterface
{
    public const API_REQUEST_LIMIT = 100;

    public const ERROR_GET_SUBSCRIBER_NOT_EXISTS = 1311;

    public const API_TEST_CONNECTION = 'ping';

    public const API_GET_LISTS = 'subscribers_list/lists';

    // todo consider if this should be public
    public const API_ADD_SUBSCRIBER = 'subscriber/add';
    public const API_EDIT_SUBSCRIBER = 'subscriber/edit';
    public const API_GET_SUBSCRIBER = 'subscriber/get';
    public const API_DELETE_SUBSCRIBER = 'subscriber/delete';

    public const API_ADD_MULTIPLE_SUBSCRIBERS = 'subscriber/addMultiple';
    public const API_EDIT_MULTIPLE_SUBSCRIBERS = 'subscriber/editMultiple';
    public const API_GET_MULTIPLE_SUBSCRIBERS = 'subscriber/getMultiple';

    public const API_INTEGRATIONS = 'integrations';

    public function getLists(): array;

    public function addSubscriber(RequestData\Subscriber\AddInterface $subscriberAdd): void;

    public function editSubscriber(RequestData\Subscriber\EditInterface $subscriberEdit): void;

    public function getSubscriber(RequestData\Subscriber\GetInterface $subscriberGet): ResponseInterface;

    public function deleteSubscriber(RequestData\Subscriber\DeleteInterface $subscriberDelete): void;

    public function addMultipleSubscribers(RequestData\Subscriber\AddMultipleInterface $subscriberAddMultiple): void;

    public function editMultipleSubscribers(RequestData\Subscriber\EditMultipleInterface $editMultiple): void;

    /**
     * @throws Exception
     */
    public function getMultipleSubscribers(
        RequestData\Subscriber\GetMultipleInterface $subscriberGetMultiple
    ): ResponseInterface;

    /**
     * @throws ClientException
     * @throws RequestException
     * @throws ApiException
     */
    public function integrations(RequestData\IntegrationsInterface $integrationData): void;
}
