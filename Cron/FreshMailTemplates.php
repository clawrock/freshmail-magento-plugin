<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Magento\Email\Model\Template;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Virtua\FreshMail\Api\TemplateRepositoryInterface;
use Virtua\FreshMail\Api\TemplateServiceInterface;
use Virtua\FreshMail\Logger\Logger;
use Virtua\FreshMail\Model\Cron\ScheduleFreshMailTemplates;
use Virtua\FreshMail\Api\ResponseData;

class FreshMailTemplates
{
    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    /**
     * @var TemplateServiceInterface
     */
    protected $templateService;

    /**
     * @var ScheduleFreshMailTemplates
     */
    private $scheduleFreshMailTemplates;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        TemplateServiceInterface $templateService,
        TemplateRepositoryInterface $templateRepository,
        ScheduleFreshMailTemplates $scheduleFreshMailTemplates,
        Logger $logger
    ) {
        $this->templateRepository = $templateRepository;
        $this->templateService = $templateService;
        $this->scheduleFreshMailTemplates = $scheduleFreshMailTemplates;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $templates = $this->templateService->getTemplatesFromFreshMail();

            foreach ($templates as $template) {
                $this->processFreshMailTemplate($template);
            }
        } catch (\Throwable $e) {
            $this->logger->debug($e);
        }

        $this->removeJobConfig(); //TODO check is it needed
    }

    /**
     * @throws CouldNotSaveException
     * @throws IntegrationException
     * @throws NoSuchEntityException
     */
    private function processFreshMailTemplate(ResponseData\Templates\TemplateInterface $freshMailTemplate): void
    {
        $template = $this->templateRepository->getTemplateByFreshMailIdHash($freshMailTemplate->getIdHash());

        if (! $template->getId()) {
            $template->setTemplateCode($this->getTemplateNameForMagento($template, $freshMailTemplate->getName()));
            $template->setTemplateSubject($freshMailTemplate->getName());
        }

        $template->setTemplateText(
            $this->templateService->transformFreshMailTemplateToMagentoFormat($freshMailTemplate)
        );
        $template->setFreshmailAdditionalText(
            $this->templateService->transformFreshMailTemplateAdditionalTextToMagentoFormat($freshMailTemplate)
        );
        $template->setAddedAt($freshMailTemplate->getCreatedOn());
        $template->setFreshmailIdHash($freshMailTemplate->getIdHash());
        $template->setTemplateType(Template::TYPE_HTML);

        $this->templateRepository->save($template);
    }

    /**
     * @throws IntegrationException
     */
    private function getTemplateNameForMagento(Template $template, string $name): string
    {
        if ($this->checkTemplateNameExists($template, $name)) {
            $count = 2;
            $exists = true;
            $newName = $name . ' ' . $count;
            while ($exists === true) {
                $exists = $this->checkTemplateNameExists($template, $newName);
                if ($exists) {
                    $count++;
                    $newName = $name . ' ' . $count;
                }
            }

            return $newName;
        }

        return $name;
    }

    private function checkTemplateNameExists(Template $template, string $name): bool
    {
        try {
            $templateByCode = $this->templateRepository->getTemplateByCode($name);
            if ($templateByCode->getId() !== $template->getId()) {
                return true;
            }
        } catch (NoSuchEntityException $e) { }

        return false;
    }

    private function removeJobConfig(): void
    {
        $this->scheduleFreshMailTemplates->removeGetTemplatesCronJob();
    }
}
