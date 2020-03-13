<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Adminhtml\CookieGroup;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieGroup;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractCookieGroup
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
        $resultPage->setActiveMenu('Amasty_GdprCookie::cookie_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Groups'));
        $resultPage->addBreadcrumb(__('Cookie Groups'), __('Cookie Groups'));

        return $resultPage;
    }
}
