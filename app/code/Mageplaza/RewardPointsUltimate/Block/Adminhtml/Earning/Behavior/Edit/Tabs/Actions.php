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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Earning\Behavior\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsUltimate\Model\Source\PointActions;
use Mageplaza\RewardPointsUltimate\Model\Source\PointPeriod;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Earning\Behavior\Edit\Tabs
 */
class Actions extends Generic implements TabInterface
{
    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('behavior_earning_rule');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Behavior Action')]);
        $fieldset->addField('action', 'select', [
            'label'  => __('Actions'),
            'title'  => __('Actions'),
            'name'   => 'action',
            'values' => PointActions::getOptionArray()
        ]);
        $fieldset->addField('point_amount', 'text', [
            'label'    => __('Fixed amount'),
            'title'    => __('Fixed amount'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'required' => true,
            'name'     => 'point_amount',
        ]);
        $fieldset->addField('max_point', 'text', [
            'label' => __('Maximum Earning Points'),
            'title' => __('Maximum Earning Points'),
            'name'  => 'max_point',
            'note'  => __('Set the maximum number of spending points. If empty or zero, there is no limitation')
        ]);
        $fieldset->addField('max_point_period', 'select', [
            'label'  => __('Max point earn period'),
            'title'  => __('Max point earn period'),
            'name'   => 'max_point_period',
            'values' => PointPeriod::getOptionArray(),
        ]);

        if ($model->getRuleId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
