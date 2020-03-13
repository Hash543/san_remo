<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

use Magento\Framework\Api\SearchCriteriaBuilder;

class Grid extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('PSS_CRM::crm');
        $resultPage->addBreadcrumb(__('PSS'), __('PSS'));
        $resultPage->addBreadcrumb(__('SanRemo CRM'), __('SanRemo CRM'));
        $resultPage->getConfig()->getTitle()->prepend(__('PSS SanRemo CRM - Process Queue Items'));
        return $resultPage;
    }
}