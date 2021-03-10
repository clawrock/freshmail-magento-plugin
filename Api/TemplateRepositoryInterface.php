<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\Template;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TemplateRepositoryInterface
{
    public const FRESHMAIL_TEMPLATE_ID_HASH = 'freshmail_id_hash';

    public function getTemplateByFreshMailIdHash(string $idHash): Template;

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(Template $template): void;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $templateId): Template;

    /**
     * @throws NoSuchEntityException
     */
    public function getTemplateByCode(string $templateCodeName): Template;

    /**
     * @throws IntegrationException
     */
    public function getTemplates(): TemplateResource\Collection;
}
