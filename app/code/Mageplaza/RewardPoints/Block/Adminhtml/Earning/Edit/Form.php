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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Block\Adminhtml\Earning\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Website;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Model\Source\Direction;

/**
 * Class Form
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Earning\Edit
 */
class Form extends Generic
{
    /**
     * @var int earning direction
     */
    protected $currentDirection = Direction::MONEY_TO_POINT;

    /**
     * @var \Magento\Config\Model\Config\Source\Website
     */
    protected $_websites;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $_customerGroupsFactory;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Website $websites
     * @param CustomerGroupFactory $customerGroupsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Website $websites,
        CustomerGroupFactory $customerGroupsFactory,
        array $data = []
    ) {
        $this->_websites = $websites;
        $this->_customerGroupsFactory = $customerGroupsFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get reward rate
     * @return mixed
     */
    public function getRewardRate()
    {
        return $this->_coreRegistry->registry('reward_rate');
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $rewardRate = $this->getRewardRate();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
            ],
        ]);
        $form->setFieldNameSuffix('reward_rate');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => $this->isEarningRate() ? __('Earning Rate Information') : __('Spending Rate Information')
        ]);

        if ($rewardRate->getId()) {
            $fieldset->addField('rate_id', 'hidden', ['name' => 'rate_id']);
        }

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', [
                'name'     => 'website_ids',
                'title'    => __('Website'),
                'label'    => __('Website'),
                'required' => true,
                'values'   => $this->_websites->toOptionArray()
            ]);
        }

        $fieldset->addField('customer_group_ids', 'multiselect', [
            'name'     => 'customer_group_ids',
            'title'    => __('Customer Group(s)'),
            'required' => true,
            'label'    => __('Customer Group(s)'),
            'values'   => $this->getCustomerGroup()
        ]);

        $fieldset->addField('reward_rate', 'Mageplaza\RewardPoints\Block\Adminhtml\Render\Field\RewardRate', [
            'label'    => $this->isEarningRate() ? __('Earning Rate') : __('Spending Rate'),
            'subject'  => $this,
            'required' => true,
            'note'     => $this->isEarningRate() ? __('The ratio of spends to points earned for that') : __('The ratio of points spent to a discount')
        ]);

        $fieldset->addField('priority', 'text', [
            'name'  => 'priority',
            'label' => __('Priority'),
            'title' => __('Priority'),
            'class' => 'validate-number integer',
            'note'  => __('If you have similar rates, set the priority of this one (lower number means higher priority with 0 being the highest)'),
        ]);

        $form->setUseContainer(true);
        $form->setValues($rewardRate->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Is Money to point
     * @return bool
     */
    public function isEarningRate()
    {
        return $this->currentDirection == Direction::MONEY_TO_POINT;
    }

    /**
     * Get customer group
     * @return mixed
     */
    public function getCustomerGroup()
    {
        $customerGroup = $this->_customerGroupsFactory->create()->toOptionArray();

        return $customerGroup;
    }
}
