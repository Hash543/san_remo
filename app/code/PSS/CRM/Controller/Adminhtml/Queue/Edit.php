<?php
/**
 * @copyright (c) 2018. Alfa9 (http://www.alfa9.com)
 * @author Xavier Sanz <xsanz@alfa9.com>
 * @package iese_publishing
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

/**
 * Class Edit
 * @package Alfa9\IeseInvoices\Controller\Adminhtml\Customer
 */
class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $_registry;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Registry id of attribute option in PHP Memory
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('PSS_CRM::queue');
        $resultPage->addBreadcrumb(__('CRM Queue'), __('View Record'));
        $this->_registry->register('pss_crm_queue_edit_id', $this->getRequest()->getParam('id'));
        return $resultPage;
    }
}