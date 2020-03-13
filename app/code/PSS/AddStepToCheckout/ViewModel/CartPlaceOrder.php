<?php

namespace PSS\AddStepToCheckout\ViewModel;

class CartPlaceOrder implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url
    ) {

        $this->customerSession = $customerSession;
        $this->url = $url;
    }

    /**
     * @param \Magento\Checkout\Block\Onepage\Link $block
     * @return string
     */
    public function getUrl(\Magento\Checkout\Block\Onepage\Link $block)
    {
        if ($this->customerSession->isLoggedIn()) {
            return $block->getCheckoutUrl();
        }
        return $this->url->getUrl('steps/steps/singup');
    }

    /**
     * Get the Url of the cart Update
     * @return string
     */
    public function getUpdateCartUrl() {
        return $this->url->getUrl('steps/cart/updatePost');
    }

    /**
     * Get the url of the cart
     * @return string
     */
    public function getCartUrl(){
        return $this->url->getUrl('checkout/cart/index/');
    }
}