<?php

namespace PSS\DeleteAccount\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Newsletter\Model\Subscriber;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * Delete constructor.
     * @param Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param Subscriber $subscriber
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->registry = $registry;
        $this->subscriber = $subscriber;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('id', false);

        if ($customerId && $this->formKeyValidator->validate($this->getRequest())) {
            try {

                if ($customerId && $this->customerSession->isLoggedIn()) {
                    $this->registry->register('isSecureArea', true);

                    $subscriber = $this->subscriber->loadByCustomerId($customerId);

                    if ($subscriber->getId() && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                    ) {
                        $subscriber->unsubscribeCustomerById($customerId);
                        $this->messageManager->addSuccess(__("This email address has abandoned the subscription to the newsletter."));
                    }

                    $customer = $this->customer->load($customerId);
                    $customer->delete();

                    $this->messageManager->addSuccess(__('You deleted your account.'));
                } else {
                    $this->messageManager->addError(__('We can\'t delete the customer account right now.'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('We can\'t delete the customer account right now.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/login');
    }
}