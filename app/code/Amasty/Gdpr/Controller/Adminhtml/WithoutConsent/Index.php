<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\WithoutConsent;

use Amasty\Gdpr\Controller\Adminhtml\AbstractWithoutConsent;
use Amasty\Gdpr\Model\ConsentQueue\Notification;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractWithoutConsent
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
        $resultPage->setActiveMenu('Amasty_Gdpr::without_consent');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers Without Consent'));
        $resultPage->addBreadcrumb(__('Customers Without Consent'), __('Customers Without Consent'));

        return $resultPage;
    }
}
