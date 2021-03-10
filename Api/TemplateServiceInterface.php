<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api;

use \Exception;
use Virtua\FreshMail\Api\ResponseData;

interface TemplateServiceInterface
{
    /**
     * @return ResponseData\Templates\TemplateInterface[]
     * @throws Exception
     */
    public function getTemplatesFromFreshMail(): array;

    /**
     * @throws Exception
     */
    public function getTemplateByHash(string $hashId): ResponseData\Templates\TemplateInterface;

    public function transformFreshMailTemplateToMagentoFormat(ResponseData\Templates\TemplateInterface $template): string;

    public function transformFreshMailTemplateAdditionalTextToMagentoFormat(ResponseData\Templates\TemplateInterface $template): string;

    public function getFreshMailTemplateEditLinkByHashId(string $hash): string;
}
