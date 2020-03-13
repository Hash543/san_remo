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
 * @author    PSS Digital Team
 * @category  PSS
 * @package   PSS_Loyalty
 * @copyright Copyright (c) 2019 PSS (https://www.pss-ti.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PSS\Loyalty\Helper;

/**
 * Class Price
 * @package PSS\Loyalty\Helper
 */
class Price extends \Magento\Framework\Pricing\Helper\Data {
    /**
     * Convert and format price value for current application store
     *
     * @param float $value
     * @param bool  $format
     * @param bool  $includeContainer
     *
     * @return float|string
     */
    public function currency($value, $format = true, $includeContainer = true) {
        return $format
            ? $this->priceCurrency->convertAndFormat($value, $includeContainer, 0, null, Data::CURRENCY_CODE)
            : $this->priceCurrency->convert($value);
    }
}