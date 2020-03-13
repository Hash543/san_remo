<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_AddStepToCheckout
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PSS\AddStepToCheckout\Block;

use Magento\Framework\View\Element\Template;

class CheckoutSettings extends \Magento\Framework\View\Element\Template
{
    const CC_ENABLE_COLOR = 'addsteptocheckout_settings/checkout_design/enable_color';
    const CC_CHECKOUT_COLOR = 'addsteptocheckout_settings/checkout_design/checkout_color';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }


    public function getCheckoutColor()
    {
        return $this->scopeConfig->getValue(self::CC_CHECKOUT_COLOR, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function  isEnable()
    {
        return $this->scopeConfig->getValue(self::CC_ENABLE_COLOR, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
