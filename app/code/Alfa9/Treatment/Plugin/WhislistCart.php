<?php

namespace Alfa9\Treatment\Plugin;

class WhislistCart
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * WhislistCart constructor.
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->url = $url;
    }

    /**
     * @param \Magento\Checkout\Helper\Cart $subject
     * @param $return
     * @return bool
     */
    public function afterGetShouldRedirectToCart(
        \Magento\Checkout\Helper\Cart $subject,
        $return
    )
    {
        return true;
    }

    /**
     * @param \Magento\Checkout\Helper\Cart $subject
     * @param $return
     * @return string
     */
    public function afterGetCartUrl(
        \Magento\Checkout\Helper\Cart $subject,
        $return
    )
    {
        return $this->url->getUrl('checkout');
    }
}