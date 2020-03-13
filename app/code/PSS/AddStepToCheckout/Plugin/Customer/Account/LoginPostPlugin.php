<?php

namespace PSS\AddStepToCheckout\Plugin\Customer\Account;

class LoginPostPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
    	\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\RequestInterface $request
    )
    {
    	$this->_request = $context->getRequest();
    	$this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->request = $request;
    }

    /**
    * @param \Magento\Customer\Controller\Account\LoginPost $subject
    * @param \Magento\Framework\Controller\Result\Redirect $result
    */
    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $result)
    {
        $currentUrl = $this->request->getPost('currentUrl');

        if ($currentUrl === 'steps/steps/singup/') {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('checkout');
        } else {
        	return $result;
        }
    }
}