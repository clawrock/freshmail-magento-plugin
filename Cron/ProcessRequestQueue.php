<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Virtua\FreshMail\Api\Data\RequestQueueInterface;
use Virtua\FreshMail\Api\RequestQueueServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Api\RequestQueueRepositoryInterface;
use Virtua\FreshMail\Api\RequestQueueProcessorInterface;

class ProcessRequestQueue
{
    private const PROCESS_REQUESTS_LIMIT = 10;

    /**
     * @var RequestQueueServiceInterface
     */
    private $requestQueueService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RequestQueueRepositoryInterface
     */
    private $requestQueueRepository;

    /**
     * @var RequestQueueProcessorInterface
     */
    private $requestQueueProcessor;

    public function __construct(
        RequestQueueServiceInterface $requestQueueService,
        Config $config,
        DateTime $dateTime,
        Logger $logger,
        RequestQueueRepositoryInterface $requestQueueRepository,
        RequestQueueProcessorInterface $requestQueueProcessor
    ) {
        $this->requestQueueService = $requestQueueService;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->logger = $logger; // TODO check if it is needed
        $this->requestQueueRepository = $requestQueueRepository;
        $this->requestQueueProcessor = $requestQueueProcessor;
    }

    /**
     * @throws CouldNotSaveException
     * @throws IntegrationException
     * @throws NoSuchEntityException
     */
    public function execute(): void
    {
        if (! $this->config->isEnabled()) {
            return;
        }

        $listToProcess = $this->requestQueueRepository->getListToProcess(self::PROCESS_REQUESTS_LIMIT);
        /** @var RequestQueueInterface $request */
        foreach ($listToProcess->getItems() as $request) {
            try {
                $this->requestQueueProcessor->process($request);
                $request->setProcessedAt($this->getCurrentTime());
                $this->requestQueueService->markRequestAsSuccess($request);
            } catch (\Throwable $e) {
                $request->setProcessedAt($this->getCurrentTime());
                $request->setErrors($e->getMessage());
                $this->requestQueueService->markRequestAsFailed($request);
            }
        }
    }

    private function getCurrentTime(): string
    {
        return strftime('%Y-%m-%d %H:%M:%S', $this->dateTime->gmtTimestamp());
    }
}
