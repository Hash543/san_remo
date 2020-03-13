<?php

namespace PSS\AddStepToCheckout\Controller\Steps;

use Magento\Framework\App\Action\Context;

class Singup extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    /**
     * @var
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\SessionFactory $customerSession
    )
    {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->customerSession->create();

        if ($customerSession->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('checkout');
        }
        return $this->_pageFactory->create();
    }
}