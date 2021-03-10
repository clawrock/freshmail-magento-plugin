<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\ResourceModel\Report\AbandonedCart;

use Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\EntityFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Report;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class Collection extends AbstractCollection
{
    private $_selectedColumns = [];

    protected $_isTotals = false;

    private $tableForPeriod = [
        'daily'   => 'freshmail_abandoned_cart_aggregated_daily',
        'monthly' => 'freshmail_abandoned_cart_aggregated_monthly',
        'yearly'  => 'freshmail_abandoned_cart_aggregated_yearly',
    ];

    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Report $resource,
        AdapterInterface $connection = null
    ) {
        $resource->init($this->getTableByAggregationPeriod('daily'));
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    private function getOrderedField()
    {
        return 'qty_ordered';
    }

    public function getTableByAggregationPeriod($period)
    {
        return $this->tableForPeriod[$period];
    }

    private function _getSelectedColumns()
    {
        $connection = $this->getConnection();

        if (!$this->_selectedColumns) {
            if ($this->isTotals()) {
                $this->_selectedColumns = $this->getAggregatedColumns();
            } else {
                $this->_selectedColumns = [
                    'period' => sprintf('MAX(%s)', $connection->getDateFormatSql('period', '%Y-%m-%d')),
                    'cart_total',
                    'cart_qty',
                    'cart_recovered_total',
                    'cart_recovered_qty'
                ];
                if ('year' == $this->_period) {
                    $this->_selectedColumns['period'] = $connection->getDateFormatSql('period', '%Y');
                } elseif ('month' == $this->_period) {
                    $this->_selectedColumns['period'] = $connection->getDateFormatSql('period', '%Y-%m');
                }
            }
        }

        return $this->_selectedColumns;
    }

    private function _makeBoundarySelect($from, $to)
    {
        $connection = $this->getConnection();
        $cols = $this->_getSelectedColumns();

        $select = $connection->select()->from(
            $this->getResource()->getMainTable(),
            $cols
        )->where(
            'period >= ?',
            $from
        )->where(
            'period <= ?',
            $to
        );

       /* ->order(
        $this->getOrderedField() . ' DESC'
    );*/

        $this->_applyStoresFilterToSelect($select);

        return $select;
    }

    protected function _applyAggregatedTable()
    {
        $select = $this->getSelect();
        //if grouping by product, not by period
        if (!$this->_period) {
            $cols = $this->_getSelectedColumns();

            if ($this->_from || $this->_to) {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
                $select->from($mainTable, $cols);
            } else {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
                $select->from($mainTable, $cols);
            }

            //exclude removed products
          /*  $select->where(new \Zend_Db_Expr($mainTable . '.product_id IS NOT NULL'))->group(
                'product_id'
            )->order(
                $this->getOrderedField() . ' ' . \Magento\Framework\DB\Select::SQL_DESC
            );*/

            return $this;
        }

        if ('year' == $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } elseif ('month' == $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('monthly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } else {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
            $select->from($mainTable, $this->_getSelectedColumns());
        }
        if (!$this->isTotals()) {
            $select->group(['period']);
        }

        return $this;
    }

    public function getSelectCountSql(): Select
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    public function addStoreRestrictions($storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $currentStoreIds = $this->_storesIds;
        if (isset(
            $currentStoreIds
        ) && $currentStoreIds != \Magento\Store\Model\Store::DEFAULT_STORE_ID && $currentStoreIds != [
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ]
        ) {
            if (!is_array($currentStoreIds)) {
                $currentStoreIds = [$currentStoreIds];
            }
            $this->_storesIds = array_intersect($currentStoreIds, $storeIds);
        } else {
            $this->_storesIds = $storeIds;
        }

        return $this;
    }

    protected function _beforeLoad()
    {

        $this->checkAndChangeDatePeriod();
        //$this->_applyStoresFilter();
        parent::_beforeLoad();
        $string = $this->getSelect()->__toString();
        return $this;
    }

    private function checkAndChangeDatePeriod(): void
    {
        if ($this->_period === 'year') {
            $this->changePeriodToYearly();
        } elseif ($this->_period === 'month') {
            $this->changePeriodToMonthly();
        }
    }

    private function changePeriodToYearly(): void
    {
        if ($this->_from) {
            $periodFrom = new \DateTime($this->_from);
            $periodFrom->setDate((int) $periodFrom->format('Y'), 1, 1);
            $this->_from = $periodFrom->format('Y-m-d');
        }
        if ($this->_to) {
            $periodTo = new \DateTime($this->_to);
            $periodTo->setDate((int) $periodTo->format('Y'), 12, 31);
            $this->_to = $periodTo->format('Y-m-d');
        }
    }

    private function changePeriodToMonthly(): void
    {
        if ($this->_from) {
            $periodFrom = new \DateTime($this->_from);
            $periodFrom->setDate((int) $periodFrom->format('Y'), (int) $periodFrom->format('m'), 1);
            $this->_from = $periodFrom->format('Y-m-d');
        }
        if ($this->_to) {
            $periodTo = new \DateTime($this->_to);
            $periodTo->setDate(
                (int) $periodTo->format('Y'),
                (int) $periodTo->format('m'),
                (int) $periodTo->format('t')
            );
            $this->_to = $periodTo->format('Y-m-d');
        }
    }
}
