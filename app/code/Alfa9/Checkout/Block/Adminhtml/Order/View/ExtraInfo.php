<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Checkout\Block\Adminhtml\Order\View;

class ExtraInfo extends  \Magento\Sales\Block\Adminhtml\Order\AbstractOrder {

    /**
     * I override this constructor because I had an stupid error when my class have empty constructor in developer mode
     * ExtraInfo constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = [])
    {
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        $order = null;
        try {
            $order = parent::getOrder();
        } catch (\Exception $e){
            //Todo: Do nothing
        }
        return $order;
    }
}