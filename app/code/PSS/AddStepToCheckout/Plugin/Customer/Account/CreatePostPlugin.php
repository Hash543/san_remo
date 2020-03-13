<?php

namespace PSS\AddStepToCheckout\Plugin\Customer\Account;

class CreatePostPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }

    /*
    * @param \Magento\Customer\Controller\Account\CreatePost $subject
    * @param \Magento\Framework\Controller\Result\Redirect $result
    */
    public function afterExecute(\Magento\Customer\Controller\Account\CreatePost $subject, $result)
    {
        if($result instanceof \Magento\Framework\Controller\Result\Redirect) {
            /** \Magento\Framework\Controller\Result\Redirect $result */
            $currentUrl = $this->request->getPost('currentUrl');
            if ($currentUrl === 'steps/steps/singup/') {
                $result->setPath('checkout');
            } else {
                $result->setPath('customer/account');
            }
        }
        return $result;
    }
}