<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Customer\Plugin\Controller\Account;
/**
 * Class LoginPost
 * @package Alfa9\Customer\Plugin\Controller\Account
 */
class LoginPost {
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * LoginPost constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Customer\Controller\Account\LoginPost $loginPost
     * @param \Magento\Framework\Controller\Result\Redirect $redirect
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $loginPost, $redirect) {
        if($this->customerSession->getRequiredAttributesMissing()) {
            $redirect->setUrl($this->urlBuilder->getUrl('customer/account/edit/'));
        }
        return $redirect;
    }
}