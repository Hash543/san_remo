<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Controller\Adminhtml\AbstractRequest;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractRequest
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Gdpr::requests');
        $resultPage->getConfig()->getTitle()->prepend(__('Delete Requests'));
        $resultPage->addBreadcrumb(__('Delete Requests'), __('Delete Requests'));

        return $resultPage;
    }
}
