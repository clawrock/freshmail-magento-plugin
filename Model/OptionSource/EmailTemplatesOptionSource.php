<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\OptionSource;

use Virtua\FreshMail\Model\GetFreshMailTemplates;
use Magento\Framework\Data\OptionSourceInterface;
use Psr\Log\LoggerInterface;

class EmailTemplatesOptionSource implements OptionSourceInterface
{
    /** @var GetFreshMailTemplates  */
    private $getFreshMailTemplates;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        GetFreshMailTemplates $getFreshMailTemplates,
        LoggerInterface $logger
    ) {
        $this->getFreshMailTemplates = $getFreshMailTemplates;
        $this->logger = $logger;
    }

    public function toOptionArray(): array
    {
        $options = [[
            'value' => '',
            'label' => __('Please select')
        ]];
        try {
            $emails = $this->getFreshMailTemplates->execute();
            foreach ($emails as $email) {
                $options[] = [
                    'value' => $email['id_hash'],
                    'label' => $email['name']
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return $options;
    }
}
