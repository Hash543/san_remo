<?php


namespace PSS\ReplaceProductname\Observer\Frontend\Checkout;

class CartProductAddAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $_request;

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     *
     */

    public function __construct( \Magento\Framework\App\RequestInterface $request ) { 
        $this->_request = $request; 
    } 

    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $reqeustParams = $this->_request->getParams();
        if(isset($reqeustParams['customproductname'])){
            $customproductname = $reqeustParams['customproductname'];
            $item = $observer->getEvent()->getData('quote_item');         
            $product = $observer->getEvent()->getData('product');
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $item->getProduct()->setName($customproductname);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
}
