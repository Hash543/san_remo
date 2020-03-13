<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */
 
namespace Firebear\ConfigurableProducts\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel as OriginalConfigurablePanel;
use Magento\Ui\Component\Form;

class ConfigurablePanel
{

    /**
     * Modify configurable panel meta data.
     *
     * @param OriginalConfigurablePanel $subject
     * @param array                     $meta
     *
     * @return array
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function afterModifyMeta(
        OriginalConfigurablePanel $subject,
        $meta
    ) {

        if (isset(
            $meta[OriginalConfigurablePanel::GROUP_CONFIGURABLE]['children']
            [OriginalConfigurablePanel::CONFIGURABLE_MATRIX]['children']
            ['record']['children']
        )) {
            $defaultColumnData = [
                'default_container' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'component' => 'Firebear_ConfigurableProducts/js/form/element/radio-set',
                                'elementTmpl' => 'Firebear_ConfigurableProducts/form/components/radio-set',
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'label' => __('Default'),
                                'dataScope' => 'default_value',
                                'dataName'  => OriginalConfigurablePanel::CONFIGURABLE_MATRIX . '[default]'
                            ],
                        ],
                    ],
                ],
            ];
            $matrixChildren = $meta[OriginalConfigurablePanel::GROUP_CONFIGURABLE]['children']
                [OriginalConfigurablePanel::CONFIGURABLE_MATRIX]['children']
                ['record']['children'];

            $meta[OriginalConfigurablePanel::GROUP_CONFIGURABLE]['children']
                [OriginalConfigurablePanel::CONFIGURABLE_MATRIX]['children']
                ['record']['children'] = $defaultColumnData + $matrixChildren;
        }

        return $meta;
    }
}
