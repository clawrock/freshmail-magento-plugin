<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Cron;

use Virtua\FreshMail\Model\ResourceModel\Report\AbandonedCart;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class GenerateReports
{
    /**
     * @var \DateTime
     */
    private $fromDate;

    /**
     * @var AbandonedCart
     */
    private $abandonedCart;


    public function __construct(
        TimezoneInterface $timezone,
        AbandonedCart $abandonedCart
    ) {
        $this->abandonedCart = $abandonedCart;

        $currentDate = $timezone->date();
        $this->fromDate = $currentDate->modify('-25 hours');
    }

    public function execute(): void
    {
        $this->generateAbandonedCartReports();
    }

    public function generateAbandonedCartReports(): void
    {
        $this->abandonedCart->aggregate($this->fromDate);
    }
}
