<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\Data\FollowUpEmailInterface;
use Virtua\FreshMail\Api\Data\FollowUpEmailSearchResultsInterface;

interface FollowUpEmailRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     */
    public function save(FollowUpEmailInterface $followUpEmail): void;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $followUpEmailId): FollowUpEmailInterface;

    /**
     * @throws IntegrationException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): FollowUpEmailSearchResultsInterface;

    /**
     * @return FollowUpEmailInterface[]
     */
    public function getScheduledEmails(): array;
}
