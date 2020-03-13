<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml;

abstract class AbstractController extends \Magento\Backend\App\Action
{

    public $module = "MassStockUpdate";
    protected $_logger = null;
    protected $_resultForwardFactory = null;
    protected $_resultRedirectFactory = null;
    protected $_resultRawFactory = null;
    protected $_resultPageFactory = null;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Wyomind\MassStockUpdate\Logger\Logger $logger,
            \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_logger = $logger;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_resultRawFactory = $resultRawFactory;
        $this->_resultPageFactory = $resultPageFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_' . $this->module . '::profiles');
    }

    abstract public function execute();
}
