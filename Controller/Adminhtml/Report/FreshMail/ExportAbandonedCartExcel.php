<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\Report\FreshMail;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Virtua\FreshMail\Block\Adminhtml\Report\FreshMail\AbandonedCart\Grid;

class ExportAbandonedCartExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    public function execute()
    {
        $fileName = 'abandonedcart.xml';
        $grid = $this->_view->getLayout()->createBlock(Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
