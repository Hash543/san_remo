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

namespace PSS\Loyalty\Model\Config;

use Magento\Framework\Locale\TranslatedLists;
use Magento\Framework\Locale\Bundle\CurrencyBundle;

class AddCurrencies extends TranslatedLists {

    public function getNewCurrencies() {
        /*
           This is your function that returns an array with new
           currencies. For example:
         */
        return [
            ['value' => 'SRP', 'label' => 'San Remo Points'],
        ];
    }

    public function getOptionAllCurrencies() {
        $currencyBundle = new CurrencyBundle();
        $locale = $this->localeResolver->getLocale();
        $currencies = $currencyBundle->get($locale)['Currencies'] ? : [];

        $options = [];
        foreach($currencies as $code => $data) {
            $options[] = ['label' => $data[1], 'value' => $code];
        }

        $options = array_merge($options, $this->getNewCurrencies());

        return $this->_sortOptionArray($options);
    }

    public function getOptionCurrencies() {
        $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'] ? : [];
        $options = [];
        $allowed = $this->_config->getAllowedCurrencies();
        foreach($currencies as $code => $data) {
            if(!in_array($code, $allowed)) {
                continue;
            }
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        $options = array_merge($options, $this->getNewCurrencies());

        return $this->_sortOptionArray($options);
    }
}