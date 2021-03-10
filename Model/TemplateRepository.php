<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model;

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\TemplateRepositoryInterface;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var TemplateResource
     */
    private $templateResourceModel;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        TemplateFactory $templateFactory,
        TemplateResource $templateResourceModel,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateResourceModel = $templateResourceModel;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function getTemplateByFreshMailIdHash(string $idHash): Template
    {
        $template = $this->templateFactory->create(['data' => ['area' => 'frontend']]);
        $this->templateResourceModel->load($template, $idHash, self::FRESHMAIL_TEMPLATE_ID_HASH);

        return $template;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Template $template): void
    {
        try {
            $this->templateResourceModel->save($template);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save email template: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $templateId): Template
    {
        $template = $this->templateFactory->create(['data' => ['area' => 'frontend']]);
        $this->templateResourceModel->load($template, $templateId);

        if (! $template->getId()) {
            throw NoSuchEntityException::singleField('template_id', $templateId);
        }

        return $template;
    }

    /**
     * @throws IntegrationException
     */
    private function getCollection(SearchCriteriaInterface $searchCriteria): TemplateResource\Collection
    {
        try {
            $collection = $this->collectionFactory->create();
            $this->collectionProcessor->process($searchCriteria, $collection);

            return $collection;
        } catch (\Throwable $e) {
            $message = __('An error occurred during get template collection: %error', ['error' => $e->getMessage()]);
            throw new IntegrationException($message, $e);
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getTemplateByCode(string $templateCodeName): Template
    {
        $template = $this->templateFactory->create(['data' => ['area' => 'frontend']]);
        $this->templateResourceModel->load($template, $templateCodeName, 'template_code');

        if (! $template->getId()) {
            throw NoSuchEntityException::singleField('template_code', $template);
        }

        return $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplates(): TemplateResource\Collection
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->getCollection($searchCriteria);
    }
}
