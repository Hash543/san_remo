<?php
/**
 * @author Israel Yasis
 */
namespace PSS\PaymentPoints\Model;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {
    /**
     * @var \PSS\PaymentPoints\Helper\Data 
     */
    private $helperPoints;
    /**
     * ConfigProvider constructor.
     * @param \PSS\PaymentPoints\Helper\Data $helperPoints
     */
    public function __construct(
        \PSS\PaymentPoints\Helper\Data $helperPoints
    ) {
        $this->helperPoints = $helperPoints;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig() {
        return [
            'points_show_slider' => $this->helperPoints->showSlider()
        ];
    }
}