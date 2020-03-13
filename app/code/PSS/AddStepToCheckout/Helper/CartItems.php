<?php

namespace PSS\AddStepToCheckout\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class CartItems extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    public function __construct(Context $context, \Magento\Checkout\Model\Session $session)
    {
        parent::__construct($context);
        $this->session = $session;
    }
    public function getAllItemsCarts (){
        /*$quote = $this->session->getQuote();
        $items = $quote->getAllVisibleItems();
        $cartDataCount = count( $items );
        var_dump($cartDataCount); die;
        return $cartDataCount;*/
        $items = $this->session->getQuote()->getAllItems();
        return $items;
    }

    public function getSubTotal(){
        $subtotal = $this->session->getQuote()->getSubtotal();
        return $subtotal;
    }

}