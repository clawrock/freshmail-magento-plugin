<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\Report\FreshMail;

use Virtua\FreshMail\Model\Flag;
use Magento\Reports\Controller\Adminhtml\Report\Sales;

class AbandonedCart extends Sales
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Virtua_Freshmail::report_abandonedcart');
    }

    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_ABANDONED_CART_FLAG_CODE, 'abandoned_carts');


        $this->_initAction()->_setActiveMenu(
            'Virtua_FreshMail::report_abandonedcart'
        )->_addBreadcrumb(
            __('Report'),
            __('Abandoned Carts')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Abandoned Carts'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_report_freshMail_abandonedCart.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
