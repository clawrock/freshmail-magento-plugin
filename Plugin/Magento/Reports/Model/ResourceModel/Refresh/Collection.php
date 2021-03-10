<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Plugin\Magento\Reports\Model\ResourceModel\Refresh;

use Magento\Reports\Model\FlagFactory;
use Virtua\FreshMail\Model\Flag;

class Collection
{
    private const REPORT_ID = 'abandoned_carts';

    /**
     * @var FlagFactory
     */
    private $reportsFlagFactory;

    public function __construct(
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory
    ) {
        $this->reportsFlagFactory = $reportsFlagFactory;
    }

    public function afterLoadData(
        \Magento\Reports\Model\ResourceModel\Refresh\Collection $subject,
        \Magento\Reports\Model\ResourceModel\Refresh\Collection $result,
        bool $printQuery = false,
        bool $logQuery = false
    ): \Magento\Reports\Model\ResourceModel\Refresh\Collection {
        try{
            $item = new \Magento\Framework\DataObject();
            $item->setData([
                'id' => self::REPORT_ID,
                'report' => __('Abandoned Carts'),
                'comment' => __('Abandoned Carts'),
                'updated_at' => $this->_getUpdatedAt(Flag::REPORT_ABANDONED_CART_FLAG_CODE)
            ]);
            $result->addItem($item);
        } catch (\Exception $e) {}

        return $result;
    }

    private function _getUpdatedAt($reportCode): string
    {
        $flag = $this->reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }
}
