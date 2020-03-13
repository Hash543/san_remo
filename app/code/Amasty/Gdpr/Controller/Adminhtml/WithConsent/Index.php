<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\WithConsent;

use Amasty\Gdpr\Controller\Adminhtml\AbstractWithConsent;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Amasty\Gdpr\Model\ConsentQueue\Notification;

class Index extends AbstractWithConsent
{
    /**
     * @var Notification
     */
    private $notification;

    public function __construct(
        Context $context,
        Notification $notification
    ) {
        parent::__construct($context);
        $this->notification = $notification;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->notification->addQueueLinkNotice();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Gdpr::with_consent');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers With Consent'));
        $resultPage->addBreadcrumb(__('Customers With Consent'), __('Customers With Consent'));

        return $resultPage;
    }
}
