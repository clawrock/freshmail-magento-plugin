<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Cron;

use Magento\Cron\Observer\ProcessCronQueueObserver;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ScheduleFreshMailTemplates
{
    private const GET_TEMPLATES_JOB_CODE = 'freshmail_get_email_templates';
    private const GET_TEMPLATES_CONFIG_PATH =
        'crontab/freshmail/jobs/' . self::GET_TEMPLATES_JOB_CODE . '/schedule/cron_expr';
    private const GET_TEMPLATES_CRON_SCHEDULE_AHEAD = '2'; // minutes

    /**
     * @var WriterInterface
     */
    private $configWriter;

    private $dateTime;

    public function __construct(
        WriterInterface $configWriter,
        DateTime $dateTime
    ) {
        $this->configWriter = $configWriter;
        $this->dateTime = $dateTime;
    }

    public function scheduleGetTemplatesJob(): void
    {
        $this->saveCronExpressionInConfig($this->getCronExpression());
    }

    public function removeGetTemplatesCronJob(): void
    {
        $this->configWriter->delete(self::GET_TEMPLATES_CONFIG_PATH);
    }

    private function getCronExpression(): string
    {
        $scheduleTimestamp = (int) ($this->dateTime->gmtTimestamp()
            + (self::GET_TEMPLATES_CRON_SCHEDULE_AHEAD * ProcessCronQueueObserver::SECONDS_IN_MINUTE));

        $minutes = strftime('%M', $scheduleTimestamp);
        return join(' ', [$minutes, '*', '*', '*', '*']);
    }

    private function saveCronExpressionInConfig(string $cronExpression): void
    {
        $this->configWriter->save(self::GET_TEMPLATES_CONFIG_PATH, $cronExpression);
    }



}
