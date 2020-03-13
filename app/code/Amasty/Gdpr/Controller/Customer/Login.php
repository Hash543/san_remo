<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\Consent;
use Amasty\Gdpr\Model\ConsentQueue\Email;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class Login extends AbstractAccount
{
    /**
     * @var Email
     */
    private $email;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Consent
     */
    private $consent;

    public function __construct(
        Context $context,
        Email $email,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        Consent $consent
    ) {
        parent::__construct($context);
        $this->email = $email;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->consent = $consent;
    }

    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $customerId = (int)$params['customer_id'];
            $requestKey = $params['key'];
            $generatedKey = $this->email->generateKey($customerId);
            if ($requestKey == $generatedKey) {
                $customerIsLoggedIn = $this->customerSession->isLoggedIn();
                if ($customerIsLoggedIn && ($customerId != $this->customerSession->getCustomerId())) {
                    $this->customerSession->logout();
                    $customerIsLoggedIn = false;
                }

                if (!$customerIsLoggedIn) {
                    $customer = $this->customerRepository->getById($customerId);
                    if ($customer->getId()) {
                        $this->customerSession->setCustomerDataAsLoggedIn($customer);
                    }
                }

                $this->consent->acceptLastVersion($customerId, Consent::FROM_EMAIL);
                $this->messageManager->addSuccessMessage(
                    __('Thank you for your cooperation. Your consent was recorded.')
                );
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong.')
            );
            $this->logger->critical($exception);
        }

        $this->_redirect('customer/account');
    }
}
