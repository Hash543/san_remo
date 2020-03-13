<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin;

use Amasty\Base\Model\Serializer;
use Amasty\Promo\Model\RuleFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

class ValueProvider
{
    const TOPBANNERS = [
        'ampromorule_top_banner_image',
        'ampromorule_top_banner_alt',
        'ampromorule_top_banner_on_hover_text',
        'ampromorule_top_banner_link',
        'ampromorule_top_banner_show_gift_images',
        'ampromorule_top_banner_description',
    ];

    const AFTERBANNERS = [
        'ampromorule_after_product_banner_image',
        'ampromorule_after_product_banner_alt',
        'ampromorule_after_product_banner_on_hover_text',
        'ampromorule_after_product_banner_link',
        'ampromorule_after_product_banner_show_gift_images',
        'ampromorule_after_product_banner_description',
    ];

    const ACTIONS = [
        'ampromorule[sku]',
        'ampromorule[type]',
    ];

    const BOOL_ACTIONS = [
        'ampromorule[apply_tax]',
        'ampromorule[apply_shipping]',
    ];

    const LABELS = [
        'ampromorule_label_image',
        'ampromorule_label_image_alt',
    ];

    const IMAGE = [
        'after_product_banner_image',
        'top_banner_image',
        'label_image',
    ];

    const FIELDSETS = [
        'actions',
        'ampromorule_top_banner',
        'ampromorule_after_product_banner',
        'ampromorule_product_label',
        'ampromorule_items_price'
    ];

    const PRICE = [
        'ampromorule_items_discount',
        'ampromorule_minimal_items_price',
    ];

    /**
     * @var RuleFactory
     */
    private $ruleFactory;
    /**
     * @var Serializer
     */
    private $serializerBase;

    public function __construct(RuleFactory $ruleFactory, Serializer $serializerBase)
    {
        $this->ruleFactory = $ruleFactory;
        $this->serializerBase = $serializerBase;
    }

    /**
     * @param SalesRuleValueProvider $subject
     * @param \Closure $proceed
     * @param Rule $rule
     *
     * @return array
     */
    public function aroundGetMetadataValues(
        SalesRuleValueProvider $subject,
        \Closure $proceed,
        Rule $rule
    ) {
        $result = $proceed($rule);

        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];

        $autoaddActions[] = [
            'label' => __('Auto add promo items with products'),
            'value' => 'ampromo_items'
        ];
        $autoaddActions[] = [
            'label' => __('Auto add promo items for the whole cart'),
            'value' => 'ampromo_cart'
        ];
        $autoaddActions[] = [
            'label' => __('Auto add the same product'),
            'value' => 'ampromo_product'
        ];
        $autoaddActions[] = [
            'label' => __('Auto add promo items for every $X spent'),
            'value' => 'ampromo_spent'
        ];
        $autoaddActions[] = [
            'label' => __('Add gift with each N-th product in the cart'),
            'value' => 'ampromo_eachn'
        ];

        $actions[] = [
            'label' => __('Automatically add products to cart'),
            'value' => $autoaddActions
        ];

        /** @var \Amasty\Promo\Model\Rule $ampromoRule */
        $ampromoRule = $this->ruleFactory->create();
        $ampromoRule->load($rule->getId(), 'salesrule_id');

        foreach (self::FIELDSETS as $fieldSet) {
            $result = $this->setValueForFields($result, $fieldSet, $ampromoRule);
            $result = $this->setComponentTypeForFields($result, $fieldSet);
        }

        return $result;
    }

    /**
     * set component type to field for schedule update
     *
     * @param $result
     * @param $field
     *
     * @return array
     */
    private function setComponentTypeForFields($result, $field)
    {
        switch ($field) {
            case 'ampromorule_top_banner':
                foreach (self::TOPBANNERS as $topBanner) {
                    $result[$field]['children'][$topBanner]['arguments']['data']['config']['componentType'] = 'text';
                }
                break;
            case 'ampromorule_after_product_banner':
                foreach (self::AFTERBANNERS as $topBanner) {
                    $result[$field]['children'][$topBanner]['arguments']['data']['config']['componentType'] = 'text';
                }
                break;
            case 'ampromorule_product_label':
                foreach (self::LABELS as $label) {
                    $result[$field]['children'][$label]['arguments']['data']['config']['componentType'] = 'text';
                }
                break;
            case 'actions':
                foreach (self::ACTIONS as $action) {
                    $result[$field]['children'][$action]['arguments']['data']['config']['componentType'] = 'text';
                }
                foreach (self::BOOL_ACTIONS as $action) {
                    $result[$field]['children'][$action]['arguments']['data']['config']['componentType'] = 'boolean';
                }
                break;
        }

        return $result;
    }

    /**
     * set value for amasty field in other field sets
     *
     * @param $result
     * @param $field
     * @param \Amasty\Promo\Model\Rule $rule
     *
     * @return array
     *
     */
    private function setValueForFields($result, $field, $rule)
    {
        switch ($field) {
            case 'ampromorule_top_banner':
                $result = $this->setValueForAmastyFieldSet($field, self::TOPBANNERS, $rule, $result);
                break;
            case 'ampromorule_after_product_banner':
                $result = $this->setValueForAmastyFieldSet($field, self::AFTERBANNERS, $rule, $result);
                break;
            case 'ampromorule_product_label':
                $result = $this->setValueForAmastyFieldSet($field, self::LABELS, $rule, $result);
                break;
            case 'ampromorule_items_price':
                $result = $this->setValueForAmastyFieldSet($field, self::PRICE, $rule, $result);
                break;
            case 'actions':
                $actions = array_merge(self::ACTIONS, self::BOOL_ACTIONS);
                foreach ($actions as $action) {
                    preg_match('/.+?\[(.+?)\]/', $action, $modelField);
                    $value = $rule->getData($modelField[1]);
                    $result[$field]['children']["ampromorule[$modelField[1]]"]['arguments']['data']['config']['value']
                           = $value;
                }
                break;
        }

        return $result;
    }

    /**
     * set value for amasty field set
     *
     * @param $type
     * @param $arrayWithField
     * @param \Amasty\Promo\Model\Rule $rule
     * @param $result
     *
     * @return array
     */
    private function setValueForAmastyFieldSet($type, $arrayWithField, $rule, $result)
    {
        foreach ($arrayWithField as $field) {
            $promoAttribute = str_replace('ampromorule_', '', $field);
            $value = $rule->getData($promoAttribute);
            if (in_array($promoAttribute, self::IMAGE) && $value === null) {
                continue;
            } elseif (in_array($promoAttribute, self::IMAGE) && $value !== null) {
                $value = $this->serializerBase->unserialize($value);
            }
            $result[$type]['children'][$field]['arguments']['data']['config']['value'] = $value;
        }

        return $result;
    }
}
