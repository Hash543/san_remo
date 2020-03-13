<?php

namespace Firebear\ConfigurableProducts\Plugin\Block\Product\View\Options;

class TypeSelect
{
    private $registry;
    protected $pricingHelper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->registry = $registry;
        $this->pricingHelper = $pricingHelper;
    }

    public function aroundGetValuesHtml(
        \Magento\Catalog\Block\Product\View\Options\Type\Select $subject,
        callable $proceed
    ) {
        if (!$this->registry->registry('firebear_configurable_products_abstract_plugin')) {
            return $proceed();
        }
        $_option     = $subject->getOption();
        $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store       = $subject->getProduct()->getStore();

        $subject->setSkipJsReloadPrice(1);
        // Remove inline prototype onclick and onchange events

        if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
        ) {
            $require     = $_option->getIsRequire() ? ' required' : '';
            $extraParams = '';
            $select      = $subject->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setData(
                [
                    'id'    => 'select_' . $_option->getId(),
                    'class' => $require . ' product-custom-option admin__control-select'
                ]
            );
            if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options[' . $_option->getid() . '][]');
                $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
                $currencyModel  = $objectManager->create(
                    'Magento\Directory\Model\Currency'
                );
                $price          = $_value->getPrice($_value->getPriceType());
                $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                $currencyCode   = $currencysymbol->getStore()->getCurrentCurrencyCode();
                $currencySymbol = $currencyModel->load($currencyCode)->getCurrencySymbol();
                $precision      = 2;
                $formattedPrice = $currencyModel->format(
                    $price,
                    ['symbol' => $currencySymbol, 'precision' => $precision],
                    false,
                    false
                );

                $priceStr = $formattedPrice;
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)]
                );
            }
            if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$subject->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }

        if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_RADIO
            || $_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
        ) {
            $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
            $require    = $_option->getIsRequire() ? ' required' : '';
            $arraySign  = '';
            switch ($_option->getType()) {
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_RADIO:
                    $type  = 'radio';
                    $class = 'radio admin__control-radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                            '<input type="radio" id="options_' .
                            $_option->getId() .
                            '" class="' .
                            $class .
                            ' product-custom-option" name="options[' .
                            $_option->getId() .
                            ']"' .
                            ' data-selector="options[' . $_option->getId() . ']"' .
                            ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                            ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                            $_option->getId() .
                            '"><span>' .
                            __('None') . '</span></label></div>';
                    }
                    break;
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX:
                    $type      = 'checkbox';
                    $class     = 'checkbox admin__control-checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;

                $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
                $currencyModel  = $objectManager->create(
                    'Magento\Directory\Model\Currency'
                );
                $price          = $_value->getPrice($_value->getPriceType());
                $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                $currencyCode   = $currencysymbol->getStore()->getCurrentCurrencyCode();
                $currencySymbol = $currencyModel->load($currencyCode)->getCurrencySymbol();
                $precision      = 2;
                $formattedPrice = $currencyModel->format(
                    $price,
                    ['symbol' => $currencySymbol, 'precision' => $precision],
                    false,
                    false
                );

                $priceStr = $formattedPrice;

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $dataSelector = 'options[' . $_option->getId() . ']';
                if ($arraySign) {
                    $dataSelector .= '[' . $htmlValue . ']';
                }

                $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                    $require .
                    '">' .
                    '<input type="' .
                    $type .
                    '" class="' .
                    $class .
                    ' ' .
                    $require .
                    ' product-custom-option"' .
                    ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                    ' name="options[' .
                    $_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '" value="' .
                    $htmlValue .
                    '" ' .
                    $checked .
                    ' data-selector="' . $dataSelector . '"' .
                    ' price="' .
                    $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                    '" />' .
                    '<label class="label admin__field-label" for="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '"><span>' .
                    $_value->getTitle() .
                    '</span> ' .
                    $priceStr .
                    '</label>';
                $selectHtml .= '</div>';
            }
            $selectHtml .= '</div>';

            return $selectHtml;
        }
    }
}