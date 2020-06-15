<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Virtua\FreshMail\Api\RequestQueueProcessorInterface;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;
use Virtua\FreshMail\Api\FreshMailApiInterface;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;
use Virtua\FreshMail\Api\RequestData\Subscriber;
use Virtua\FreshMail\Api\FullSyncSubscriberServiceInterface;

class RequestQueueProcessor implements RequestQueueProcessorInterface
{
    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    /**
     * @var FreshMailApiInterface|null
     */
    private $freshMailApi;

    /**
     * @var RequestQueueServiceInterface
     */
    private $requestQueueService;

    /**
     * @var Subscriber\AddInterfaceFactory
     */
    private $subscriberAddFactory;

    /**
     * @var Subscriber\EditInterfaceFactory
     */
    private $subscriberEditFactory;

    private $subscriberDeleteFactory;

    /**
     * @var FullSyncSubscriberServiceInterface
     */
    private $fullSyncService;

    public function __construct(
        FreshMailApiInterfaceFactory $freshMailApiFactory,
        RequestQueueServiceInterface $requestQueueService,
        Subscriber\AddInterfaceFactory $subscriberAddFactory,
        Subscriber\EditInterfaceFactory $subscriberEditFactory,
        Subscriber\DeleteInterfaceFactory $subscriberDeleteFactory,
        FullSyncSubscriberServiceInterface $fullSyncService
    ) {
        $this->freshMailApiFactory = $freshMailApiFactory;
        $this->requestQueueService = $requestQueueService;
        $this->subscriberAddFactory = $subscriberAddFactory;
        $this->subscriberEditFactory = $subscriberEditFactory;
        $this->subscriberDeleteFactory = $subscriberDeleteFactory;
        $this->fullSyncService = $fullSyncService;
    }

    public function process(RequestQueueInterface $requestQueue): void
    {
        // TODO: think about solving it without switch
        try {
            switch ($requestQueue->getAction()) {
                case RequestQueueInterface::ACTION_ADD_USER :
                    /** @var Subscriber\AddInterface $addSubscriber */
                    $this->actionAddUser($requestQueue);
                    break;
                case RequestQueueInterface::ACTION_EDIT_USER :
                case RequestQueueInterface::ACTION_RESIGN_USER :
                    $this->actionEditUser($requestQueue);
                    break;
                case RequestQueueInterface::ACTION_DELETE_USER :
                    $this->actionDeleteUser($requestQueue);
                    break;
                case RequestQueueInterface::ACTION_FULL_SYNC_EMAILS :
                    $this->actionFullSync($requestQueue);
                    break;
            }
        } catch (\Exception $e) {
            // TODO handle exception
            //TODO think wheter it should be marked as failed/succes here or in higher level
            throw $e;
            //$this->requestQueueService->markQueueAsFailed($requestQueue);
        }

        //$this->requestQueueService->markQueueAsSuccess($requestQueue);
    }

    private function getFreshMailApi(): FreshMailApiInterface
    {
        if (! $this->freshMailApi) {
            $this->freshMailApi = $this->freshMailApiFactory->create();
        }

        return $this->freshMailApi;
    }

    private function actionAddUser(RequestQueueInterface $requestQueue): void
    {
        $addSubscriber = $this->subscriberAddFactory->create($requestQueue->getParamsArray());
        $this->getFreshMailApi()->addSubscriber($addSubscriber);
        // todo catch already exists error
    }

    private function actionEditUser(RequestQueueInterface $requestQueue): void
    {
        $editSubscriber = $this->subscriberEditFactory->create($requestQueue->getParamsArray());
        $this->getFreshMailApi()->editSubscriber($editSubscriber);
        // todo catch does not exists error
    }

    private function actionDeleteUser(RequestQueueInterface $requestQueue): void
    {
        $deleteSubscriber = $this->subscriberDeleteFactory->create($requestQueue->getParamsArray());
        $this->getFreshMailApi()->deleteSubscriber($deleteSubscriber);
    }

    private function actionFullSync(RequestQueueInterface $requestQueue): void
    {
        $this->fullSyncService->execute();
    }
}