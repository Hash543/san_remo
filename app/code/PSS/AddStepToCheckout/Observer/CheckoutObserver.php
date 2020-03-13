<?php

namespace PSS\AddStepToCheckout\Observer;

use Magento\Framework\Event\Observer;

class CheckoutObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->customerSession->create();

        if (!$customerSession->isLoggedIn()) {
            // Redirect create account
            $createAccountUrl = $this->url->getUrl('steps/steps/singup');

            /** @var \Magento\Framework\App\Action\Action $controllerAction */
            $controllerAction = $observer->getEvent()->getData('controller_action');
            $controllerAction->getResponse()->setRedirect($createAccountUrl);
        }

        $shippingAddress = $customerSession->getDefaultTaxShippingAddress();

        /*if (count($shippingAddress) === 0) {
            $addAddress = $this->url->getUrl('customer/address/new');*/

            /** @var \Magento\Framework\App\Action\Action $controllerAction */
/*            $controller = $observer->getEvent()->getData('controller_action');
            $this->messageManager->addErrorMessage(__('Por favor cree una direccion para continuar con el pedido.'));
            $controller->getResponse()->setRedirect($addAddress);
        }*/
    }
}