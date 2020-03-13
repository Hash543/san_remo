<?php

namespace Firebear\ConfigurableProducts\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;

class BundlePanel extends \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel
{

    /**
     * Get Bundle Options structure
     *
     * @return array
     */
    protected function getBundleOptions()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => 'data.bundle_options',
                        'isDefaultFieldScope' => 'is_default',
                        'bundleSelectionsName' => 'product_bundle_container.bundle_selections'
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'headerLabel' => __('New Option'),
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => 'product_bundle_container.position',
                                'imports' => [
                                    'label' => '${ $.name }' . '.product_bundle_container.option_info.title:value'
                                ],
                            ],
                        ],
                    ],
                    'children' => [
                        'product_bundle_container' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'fieldset',
                                        'collapsible' => true,
                                        'label' => '',
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                'option_info' => $this->getOptionInfo(),
                                'position' => $this->getHiddenColumn('position', 20),
                                'option_id' => $this->getHiddenColumn('option_id', 30),
                                'delete' => $this->getHiddenColumn('delete', 40),
                                'bundle_selections' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Container::NAME,
                                                'component' =>
                                                    'Firebear_ConfigurableProducts/js/dynamic-rows/dynamic-rows-grid',
                                                'sortOrder' => 50,
                                                'additionalClasses' => 'admin__field-wide',
                                                'template' => 'ui/dynamic-rows/templates/default',
                                                'provider' => 'product_form.product_form_data_source',
                                                'dataProvider' => '${ $.dataScope }' . '.bundle_button_proxy',
                                                'identificationDRProperty' => 'product_id',
                                                'identificationProperty' => 'product_id',
                                                'map' => [
                                                    'product_id' => 'entity_id',
                                                    'name' => 'name',
                                                    'sku' => 'sku',
                                                    'price' => 'price',
                                                    'delete' => '',
                                                    'selection_can_change_qty' => '',
                                                    'selection_id' => '',
                                                    'selection_price_type' => '',
                                                    'selection_price_value' => '',
                                                    'selection_qty' => '',
                                                    'productType' => 'type_id',
                                                ],
                                                'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                                                'imports' => [
                                                    'inputType' => '${$.provider}:${$.dataScope}.type'
                                                ],
                                                'source' => 'product'
                                            ],
                                        ],
                                    ],
                                    'children' => [
                                        'record' => $this->getBundleSelections(),
                                    ]
                                ],
                                'modal_set' => $this->getModalSet(),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
