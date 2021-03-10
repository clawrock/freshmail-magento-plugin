<?php
// todo think about truncating data when aggregate is launched - whether to clean all data or not
declare(strict_types=1);

namespace Virtua\FreshMail\Model\ResourceModel\Report;

use \Exception;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\ResourceConnection;
use Virtua\FreshMail\Logger\Logger;
use DateTimeInterface;
use Virtua\FreshMail\Model\Flag;

class AbandonedCart extends AbstractReport
{
    public const AGGREGATION_DAILY = 'freshmail_abandoned_cart_aggregated_daily';
    public const AGGREGATION_MONTHLY = 'freshmail_abandoned_cart_aggregated_monthly';
    public const AGGREGATION_YEARLY = 'freshmail_abandoned_cart_aggregated_yearly';

    public const SOURCE_TABLE = 'freshmail_abandoned_cart';


    protected $resource;
    protected $timezone;

    /**
     * @var Logger
     */
    private $freshMailLogger;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param array $ignoredProductTypes
     * @param string $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        Logger $freshMailLogger,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );

        $this->resource = $resource;
        $this->timezone = $timezone;
        $this->freshMailLogger = $freshMailLogger;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    public function aggregate(DateTimeInterface $from = null, DateTimeInterface $to = null): void
    {
        try {
            $from = $from ? $from->format('Y-m-d') : null;
            $to = $to ? $to->format('Y-m-d') : null;
            $this->truncateTables($from, $to);
            $this->fillDailyTable($from, $to);
            $this->updateDailyTableWithRecoveredData();
            $this->fillMonthlyTable();
            $this->fillYearlyTable();
            $this->_setFlagData(Flag::REPORT_ABANDONED_CART_FLAG_CODE);
        } catch (Exception $e) {
            $this->freshMailLogger->error($e->getMessage());
        }
    }

    private function truncateTables(string $from = null, string $to = null): void
    {
        $this->_clearTableByDateRange(self::AGGREGATION_DAILY, $from, $to);
        $this->_clearTableByDateRange(self::AGGREGATION_MONTHLY);
        $this->_clearTableByDateRange(self::AGGREGATION_YEARLY);
    }

    private function fillDailyTable(string $from = null, string $to = null): void
    {
        // todo implement adding records in batches
        $connection = $this->getConnection();

        $periodExpr = $connection->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                ['source_table' => $this->getTable(self::SOURCE_TABLE)],
                'source_table.abandoned_at',
                $from,
                $to
            )
        );

        $select = $connection->select();

        $select->group([$periodExpr, 'source_table.store_id']);

        $columns = [
            'period' => $periodExpr,
            'store_id' => 'source_table.store_id',
            'cart_qty' => new \Zend_Db_Expr('COUNT(*)'),
            'cart_total' => new \Zend_Db_Expr('SUM(source_table.cart_total)')
        ];

        $select->from(
            ['source_table' => $this->getTable(self::SOURCE_TABLE)],
            $columns
        );

        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);
    }

    private function updateDailyTableWithRecoveredData(): void
    {
        $connection = $this->getConnection();

        $select = $connection->select();

        $periodExpr = $connection->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                ['source_table' => $this->getTable(self::SOURCE_TABLE)],
                'source_table.recovered_at',
                null,
                null
            )
        );

        $select->group([$periodExpr, 'source_table.store_id']);

        $columns = [
            'period' => $periodExpr,
            'store_id' => 'source_table.store_id',
            'cart_recovered_qty' => new \Zend_Db_Expr('COUNT(*)'),
            'cart_recovered_total' => new \Zend_Db_Expr('SUM(source_table.cart_total)')
        ];

        $select->from(
            ['source_table' => $this->getTable(self::SOURCE_TABLE)],
            $columns
        )->where(
            'source_table.recovered = ?',
            1
        )->where(
            'source_table.recovered_at IS NOT NULL',
            null
        );

        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);
    }

    private function fillMonthlyTable(): void
    {
        $period = $this->getConnection()->getDateFormatSql('source_table.period', '%Y-%m-01');
        $this->fillAggregatedTable(self::AGGREGATION_MONTHLY, $period);
    }

    private function fillYearlyTable(): void
    {
        $periodCol = $this->getConnection()->getDateFormatSql('source_table.period', '%Y-01-01');
        $this->fillAggregatedTable(self::AGGREGATION_YEARLY, $periodCol);
    }

    private function fillAggregatedTable(string $table, \Zend_Db_Expr $period): void
    {
        $connection = $this->getConnection();

        $columns = [
            'period' => $period,
            'store_id' => 'source_table.store_id',
            'cart_qty' => new \Zend_Db_Expr('SUM(source_table.cart_qty)'),
            'cart_total' => new \Zend_Db_Expr('SUM(source_table.cart_total)'),
            'cart_recovered_qty' => new \Zend_Db_Expr('SUM(source_table.cart_recovered_qty)'),
            'cart_recovered_total' => new \Zend_Db_Expr('SUM(source_table.cart_recovered_total)')

        ];

        $select = $connection->select();

        $select->from(
            ['source_table' => self::AGGREGATION_DAILY],
            $columns
        );

        $select->group([$period, 'source_table.store_id']);
        $insertQuery = $select->insertFromSelect($table, array_keys($columns));

        $connection->query($insertQuery);
    }

    public function truncateTable(): void
    {
        $tables = [
            $this->resource->getTableName(self::AGGREGATION_DAILY),
            $this->resource->getTableName(self::AGGREGATION_MONTHLY),
            $this->resource->getTableName(self::AGGREGATION_YEARLY),
        ];
        $connection = $this->resource->getConnection();

        foreach ($tables as $table) {
            $connection->truncateTable($table);
        }
    }
}
