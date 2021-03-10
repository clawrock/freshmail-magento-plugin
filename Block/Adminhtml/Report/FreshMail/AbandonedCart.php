<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Block\Adminhtml\Report\FreshMail;

use Magento\Backend\Block\Widget\Grid\Container;

class AbandonedCart extends Container
{
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Virtua_FreshMail';
        $this->_controller = 'adminhtml_report_freshMail_abandonedCart';
        $this->_headerText = __('Abandoned Carts');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/abandonedCart', ['_current' => true]);
    }
}
