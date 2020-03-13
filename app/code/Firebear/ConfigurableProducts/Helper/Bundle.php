<?php

namespace Firebear\ConfigurableProducts\Helper;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

class Bundle extends \Magento\Bundle\Helper\Catalog\Product\Configuration
{
    private $productModel = null;
    private $eavConfig = null;

    /**
     * Bundle helper constructor.
     *
     * @param \Magento\Framework\App\Helper\Context         $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     * @param \Magento\Framework\Pricing\Helper\Data        $pricingHelper
     * @param \Magento\Framework\Escaper                    $escaper
     * @param \Magento\Catalog\Model\Product                $productModel
     * @param \Magento\Eav\Model\Config                     $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->pricingHelper        = $pricingHelper;
        $this->escaper              = $escaper;

        $this->productModel = $productModel;
        $this->eavConfig    = $eavConfig;

        parent::__construct($context, $productConfiguration, $pricingHelper, $escaper);
    }

    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getBundleOptions(ItemInterface $item)
    {
        $options = [];
        $product = $item->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');

        $bundleOptionsIds = $optionsQuoteItemOption ? json_decode($optionsQuoteItemOption->getValue(), true) : [];

        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $bundleSelectionIds = json_decode($selectionsQuoteItemOption->getValue(), true);

            if (!empty($bundleSelectionIds)) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);

                foreach ($bundleOptions as $bundleOption) {
                    $bundleOptionHtml = $this->buildOptionData($bundleOption, $item);

                    if ($bundleOptionHtml['value']) {
                        $options[] = $bundleOptionHtml;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * @param               $bundleOption
     * @param               $bundleSelection
     * @param ItemInterface $item
     *
     * @return string
     */
    private function buildSelectionData($bundleOption, $bundleSelection, ItemInterface $item)
    {
        $product = $item->getProduct();

        $qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;

        $optionHtml = '';

        $serializedBuyRequest = $item->getOptionByCode('info_buyRequest');
        $buyRequest           = json_decode($serializedBuyRequest->getValue(), true);

        $optionPrice = $this->pricingHelper->currency(
            $this->getSelectionFinalPrice($item, $bundleSelection)
        );

        if ($bundleSelection->getTypeId() == 'configurable') {
            if (isset($buyRequest['only_for_checkbox_bundle'][$bundleOption->getId()][$bundleSelection->getProductId()])) {
                $superAttributes = $buyRequest['super_attribute']
                [$bundleOption->getId()][$bundleSelection->getProductId()];
                if ($qty) {
                    foreach ($superAttributes as $code => $value) {
                        $attribute        = $this->eavConfig->getAttribute('catalog_product', $code);
                        $attributeOptions = $attribute->getSource()->getAllOptions();

                        foreach ($attributeOptions as $optionData) {
                            if ($optionData['value'] == $value) {
                                $optionHtml .=
                                    '<div style="margin-left: 28px;">' .
                                    '<i>' . $attribute->getStoreLabel() . ': ' . $optionData['label'] . '</i><br />' .
                                    '</div>';
                            }
                        }
                    }
                }
            } else {
                $superAttributes = $buyRequest['super_attribute'][$bundleOption->getId()];
                if ($qty) {
                    foreach ($superAttributes as $code => $value) {
                        $attribute        = $this->eavConfig->getAttribute('catalog_product', $code);
                        $attributeOptions = $attribute->getSource()->getAllOptions();

                        foreach ($attributeOptions as $optionData) {
                            if ($optionData['value'] == $value) {
                                $optionHtml .=
                                    '<div style="margin-left: 28px;">' .
                                    '<i>' . $attribute->getStoreLabel() . ': ' . $optionData['label'] . '</i><br />' .
                                    '</div>';
                            }
                        }
                    }
                }
            }
        }

        $coHtml = $this->getCustomOptionHtml($bundleOption, $bundleSelection, $buyRequest, $this->productModel);

        return '<div class="product-item-cart-info" style="margin: 0 5px;">' .
            '<h4 style="margin:2px 0 0 0;">' . $qty . ' x ' . $this->escaper->escapeHtml($bundleSelection->getName())
            . '</h4>' .
            '<div class-"cart-details bundle-cart-details">'
            . $optionHtml . $coHtml . '</div></div>';
    }

    /**
     * @param               $bundleOption
     * @param ItemInterface $item
     *
     * @return array
     */
    private function buildOptionData($bundleOption, ItemInterface $item)
    {

        if ($bundleOption->getSelections()) {
            $bundleOptionData = ['label' => $bundleOption->getTitle(), 'value' => []];

            $bundleSelections = $bundleOption->getSelections();

            foreach ($bundleSelections as $bundleSelection) {
                $bundleOptionData['value'][] = $this->buildSelectionData($bundleOption, $bundleSelection, $item);
            }

            return $bundleOptionData;
        }

        return [];
    }

    /**
     * Get all the set current options of the current item
     *
     * @param $bundleOption
     * @param $bundleSelection
     * @param $buyRequest
     * @param $productModel
     *
     * @return string
     */
    private function getCustomOptionHtml($bundleOption, $bundleSelection, $buyRequest, $productModel)
    {
        $customOptionHtml = '';

        if (isset($buyRequest['bundle_custom_options'])
            && isset($buyRequest['bundle_custom_options'][$bundleOption->getId()])) {
            $customOptions = $buyRequest['bundle_custom_options'][$bundleOption->getId()];

            $productModel->load($bundleSelection->getId());
            foreach ($productModel->getOptions() as $option) {
                if (isset($customOptions[$option->getId()])) {
                    if (is_array($option->getValues())) {
                        foreach ($option->getValues() as $value) {
                            if (is_string($customOptions[$option->getId()])) {
                                if ($value->getOptionTypeId() == $customOptions[$option->getId()]) {
                                    $customOptionHtml .=
                                        '<div style="margin-left: 28px;">' .
                                        '<i>' .
                                        $option->getDefaultTitle() .
                                        ': ' .
                                        $value->getTitle() .
                                        ' (+ ' .
                                        $this->pricingHelper->currency($value->getDefaultPrice()) .
                                        ')</i><br />' .
                                        '</div>';
                                }
                            } else {
                                foreach ($customOptions[$option->getId()] as $coKey => $coValue) {
                                    if ($value->getOptionTypeId() == $coValue) {
                                        $customOptionHtml .=
                                            '<div style="margin-left: 28px;">' .
                                            '<i>' .
                                            $option->getDefaultTitle() .
                                            ': ' .
                                            $value->getTitle() .
                                            ' (+ ' .
                                            $this->pricingHelper->currency($value->getDefaultPrice()) .
                                            ')</i><br />' .
                                            '</div>';
                                    }
                                }
                            }
                        }
                    } else {
                        if (is_string($customOptions[$option->getId()])) {
                            $customOptionHtml .= '<div style="margin-left: 28px;">' .
                                '<i>' . $option->getDefaultTitle() . ': ' .
                                $customOptions[$option->getId()] .
                                ' (+ ' . $this->pricingHelper->currency($option->getPrice()) . ')</i><br />' .
                                '</div>';
                        }
                    }
                }
            }
        }

        return $customOptionHtml;
    }
}
