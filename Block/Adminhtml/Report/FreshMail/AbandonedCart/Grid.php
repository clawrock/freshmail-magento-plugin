<?php
declare(strict_types=1);
// todo add recovered percentage columns
namespace Virtua\FreshMail\Block\Adminhtml\Report\FreshMail\AbandonedCart;

use Magento\Reports\Block\Adminhtml\Grid\AbstractGrid;
use Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency;
use Virtua\FreshMail\Model\ResourceModel\Report\AbandonedCart\Collection as AbandonedCartCollection;
use Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;

class Grid extends AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getResourceCollectionName()
    {
        return AbandonedCartCollection::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'period',
            [
                'header' => __('Date'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn('cart_total',[
            'header' => 'Cart Total',
            'index' => 'cart_total',
            'currency_code' => $currencyCode,
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false,
            'renderer' => Currency::class,
            'rate' => $rate
        ]);

        $this->addColumn('cart_qty',[
            'header' => 'Cart Qty',
            'index' => 'cart_qty',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false,
            'header_css_class' => 'col-qty',
            'column_css_class' => 'col-qty'
        ]);

        $this->addColumn('cart_recovered_total',[
            'header' => 'Cart Recovered Total',
            'index' => 'cart_recovered_total',
            'currency_code' => $currencyCode,
            'type' => 'currency',
            'total' => 'sum',
            'sortable' => false,
            'renderer' => Currency::class,
            'rate' => $rate
        ]);


        $this->addColumn('cart_recovered_qty',[
            'header' => 'Cart Recovered Qty',
            'index' => 'cart_recovered_qty',
            'type' => 'number',
            'total' => 'sum',
            'sortable' => false,
            'header_css_class' => 'col-qty',
            'column_css_class' => 'col-qty'
        ]);


        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }


        //$this->addExportType('*/*/exportMyCustomReportCsv', __('CSV'));
        //$this->addExportType('*/*/exportMyCustomReportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

}
