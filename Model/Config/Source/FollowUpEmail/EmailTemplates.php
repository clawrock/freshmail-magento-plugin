<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Config\Source\FollowUpEmail;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\IntegrationException;
use Virtua\FreshMail\Api\TemplateRepositoryInterface;
use Virtua\FreshMail\Logger\Logger;

class EmailTemplates implements OptionSourceInterface
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var array|null
     */
    protected $templatesList = null;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        TemplateRepositoryInterface $templateRepository,
        Logger $logger
    ) {
        $this->templateRepository = $templateRepository;
        $this->logger = $logger;
    }

    private function getTemplatesSelectList(): array
    {
        if (null === $this->templatesList) {
            $fields = [[
                'value' => '0',
                'label' => '-- Please Select --',
            ]];

            $options = [];

            try {
                $collection = $this->templateRepository->getTemplates();
                $collection->load();

                $options = $collection->toOptionArray();
            } catch (IntegrationException $e) {
                $this->logger->error($e);
            }

            $this->templatesList = array_merge($fields, $options);

            array_walk(
                $this->templatesList,
                function (&$item) {
                    $item['__disableTmpl'] = true;
                }
            );
        }

        return $this->templatesList;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        return $this->getTemplatesSelectList();
    }
}
