<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Adminhtml\CookieConsent;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieConsent;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractCookieConsent
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
        $resultPage->setActiveMenu('Amasty_GdprCookie::cookie_consent');
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Consents'));
        $resultPage->addBreadcrumb(__('Cookie Consents'), __('Cookie Consents'));

        return $resultPage;
    }
}
