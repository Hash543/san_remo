<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Block\Sales\Order\Total;
use Magento\Framework\Exception\LocalizedException;
use PSS\Paypal\Constants\PayPalConstants;
/**
 * Class PaypalFee
 * @package PSS\Paypal\Block\Sales\Order
 */
class PayPalFee extends \Magento\Framework\View\Element\Template {
    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource() {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Create the PayPal Fee in the totals
     *
     * @return $this
     */
    public function initTotals() {
        $order = $this->getSource();
        if($order && $order->getData(PayPalConstants::TOTAL_CODE) > 0.001) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => $this->getNameInLayout(),
                    'label' => __('PayPal Fee'),
                    'value' => $order->getData(PayPalConstants::TOTAL_CODE) ,
                    'base_value' => $order->getData(PayPalConstants::BASE_TOTAL_CODE)
                ]
            );
             /** @var \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals | \Magento\Sales\Block\Order\Invoice\Totals | \Magento\Sales\Block\Order\Totals $parentBlock
              */
            $parentBlock = $this->getParentBlock();
            if ($this->getBeforeCondition()) {
                $parentBlock->addTotalBefore($total, $this->getBeforeCondition());
            } else {
                $parentBlock->addTotal($total, $this->getAfterCondition());
            }
        }
        return $this;
    }
}