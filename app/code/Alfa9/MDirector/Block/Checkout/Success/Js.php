<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Checkout\Success;

class Js extends \Magento\Framework\View\Element\Template  {

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Alfa9\MDirector\Helper\Data
     */
    private $helperData;
    /**
     * Js constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession,
     * @param \Alfa9\MDirector\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Alfa9\MDirector\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * Return notify url
     *
     * @param int $orderId
     * @return string
     */
    public function getNotifyUrl($orderId)
    {
        return $this->_urlBuilder->getUrl('i4mdirector/sale/notify', ['order' => $orderId]);
    }

    /**
     * @return string
     */
    public function getOrderId(){
        return $this->checkoutSession->getLastRealOrder()->getRealOrderId();
    }

    /**
     * Prevent displaying if the pixel is not available
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helperData->isPixelEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}