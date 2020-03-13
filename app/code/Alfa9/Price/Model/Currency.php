<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Price\Model;

use Magento\Framework\App\ObjectManager;

class Currency extends \Magento\Directory\Model\Currency {
    /**
     * Apply currency format to number with specific rounding precision
     *
     * @param   float $price
     * @param   int $precision
     * @param   array $options
     * @param   bool $includeContainer
     * @param   bool $addBrackets
     * @return  string
     */
    public function formatPrecision(
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    ) {
        if (!isset($options['precision'])) {
            /**
             * @var \Alfa9\Price\Helper\Data $helperPrice
             */
            $helperPrice = ObjectManager::getInstance()->get(\Alfa9\Price\Helper\Data::class); //Todo: the only way to call the Helper
            $options['precision'] = $helperPrice->getPricePrecision($precision);
        }
        if ($includeContainer) {
            return '<span class="price">' . ($addBrackets ? '[' : '') . $this->formatTxt(
                    $price,
                    $options
                ) . ($addBrackets ? ']' : '') . '</span>';
        }
        return $this->formatTxt($price, $options);
    }
}