<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\ShoppingCart\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions as RuleAction;
use Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm\Actions as ActionsForm;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Actions as ShoppingCartActions;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\ShoppingCart\Edit\Tabs
 */
class Actions extends ActionsForm
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'shopping_cart_earning_rule';

    /**
     * @var int
     */
    protected $ruleType = Type::SHOPPING_CART_EARNING;

    /**
     * @var \Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Actions
     */
    protected $shoppingCartActions;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $fieldFactory;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ShoppingCartActions $shoppingCartActions
     * @param RuleAction $ruleActions
     * @param Yesno $sourceYesno
     * @param Fieldset $rendererFieldset
     * @param FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ShoppingCartActions $shoppingCartActions,
        RuleAction $ruleActions,
        Yesno $sourceYesno,
        Fieldset $rendererFieldset,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->shoppingCartActions = $shoppingCartActions;
        $this->sourceYesno = $sourceYesno;
        $this->fieldFactory = $fieldFactory;

        parent::__construct($context, $registry, $formFactory, $ruleActions, $rendererFieldset, $data);
    }

    /**
     * @param $form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addExtraFieldset($form)
    {
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Earning Point Action')]);
        $fieldset->addField('rule_type', 'hidden', ['name' => 'rule_type']);
        $action = $fieldset->addField('action', 'select', [
            'label'  => __('Action'),
            'title'  => __('Action'),
            'name'   => 'action',
            'values' => $this->shoppingCartActions->toOptionArray(),
            'note'   => __('select the type to earn points'),
        ]);
        $fieldset->addField('point_amount', 'text', [
            'label'    => __('Points (X)'),
            'title'    => __('Points (X)'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'required' => true,
            'name'     => 'point_amount',
        ]);
        $moneyStep = $fieldset->addField('money_step', 'text', [
            'label'    => __('Money Step (Y)'),
            'title'    => __('Money Step (Y)'),
            'required' => true,
            'name'     => 'money_step',
        ]);
        $qtyStep = $fieldset->addField('qty_step', 'text', [
            'label'    => __('Quantity Step (Y)'),
            'title'    => __('Quantity Step (Y)'),
            'required' => true,
            'name'     => 'qty_step',
        ]);
        $maxPoints = $fieldset->addField('max_points', 'text', [
            'label' => __('Maximum Earning Points By This Rule'),
            'title' => __('Maximum Earning Points By This Rule'),
            'name'  => 'max_points',
        ]);
        $applyToShipping = $fieldset->addField('apply_to_shipping', 'select', [
            'label'  => __('Apply to shipping'),
            'title'  => __('Apply to shipping'),
            'name'   => 'apply_to_shipping',
            'values' => $this->sourceYesno->toOptionArray()
        ]);
        $fieldset->addField('stop_rules_processing', 'select', [
            'label'  => __('Stop further rules processing'),
            'title'  => __('Stop further rules processing'),
            'name'   => 'stop_rules_processing',
            'values' => $this->sourceYesno->toOptionArray()
        ]);

        $blockDependence = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');
        $blockDependence->addFieldMap($action->getHtmlId(), $action->getName())
            ->addFieldMap($moneyStep->getHtmlId(), $moneyStep->getName())
            ->addFieldDependence($moneyStep->getName(), $action->getName(), ShoppingCartActions::TYPE_PRICE)
            ->addFieldMap($qtyStep->getHtmlId(), $qtyStep->getName())
            ->addFieldDependence($qtyStep->getName(), $action->getName(), ShoppingCartActions::TYPE_QTY)
            ->addFieldMap($maxPoints->getHtmlId(), $maxPoints->getName())
            ->addFieldDependence($maxPoints->getName(), $action->getName(), $this->fieldFactory->create(
                [
                    'fieldData'   => [
                        'value'     => implode(',', [ShoppingCartActions::TYPE_PRICE, ShoppingCartActions::TYPE_QTY]),
                        'separator' => ','
                    ],
                    'fieldPrefix' => ''
                ]
            ))
            ->addFieldMap($applyToShipping->getHtmlId(), $applyToShipping->getName())
            ->addFieldDependence($applyToShipping->getName(), $action->getName(), $this->fieldFactory->create(
                [
                    'fieldData'   => [
                        'value'     => implode(',', [ShoppingCartActions::TYPE_PRICE, ShoppingCartActions::TYPE_FIXED]),
                        'separator' => ','
                    ],
                    'fieldPrefix' => ''
                ]
            ));

        $this->setChild('form_after', $blockDependence);

        return $this;
    }
}
