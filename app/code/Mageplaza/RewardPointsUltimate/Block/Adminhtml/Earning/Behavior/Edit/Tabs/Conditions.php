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
use Magento\Config\Model\Config\Source\Email\Identity;
use Magento\Config\Model\Config\Source\Email\Template;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class Conditions
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Earning\Behavior\Edit\Tabs
 */
class Conditions extends Generic implements TabInterface
{
    const DEFAULT_TEMPLATE_PATH = 'rewardpoints/email/birthday/template';

    /**
     * @var \Magento\Config\Model\Config\Source\Email\Identity
     */
    protected $identity;

    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    protected $emailTemplate;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $fieldFactory;

    /**
     * Conditions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Identity $identity
     * @param Template $emailTemplate
     * @param FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Identity $identity,
        Template $emailTemplate,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->identity = $identity;
        $this->emailTemplate = $emailTemplate;
        $this->fieldFactory = $fieldFactory;
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Conditions')]);

        $pointAction = $fieldset->addField('point_action', 'select', [
            'label'  => __('Customer Action or Event'),
            'title'  => __('Customer Action or Event'),
            'name'   => 'point_action',
            'values' => CustomerEvents::getOptionArray()
        ]);
        $minWords = $fieldset->addField('min_words', 'text', [
            'label'    => __('Minimum number of words in the review'),
            'title'    => __('Minimum number of words in the review'),
            'name'     => 'min_words',
            'class'    => 'validate-digits',
            'required' => true
        ]);
        $fbAppId = $fieldset->addField('fb_app_id', 'text', [
            'label'    => __('App Id'),
            'title'    => __('App Id'),
            'name'     => 'fb_app_id',
            'required' => true,
            'note'     => __('Create app id <a href="https://developers.facebook.com" target="_blank">here</a>. '),
        ]);
        $isPurchased = $fieldset->addField('is_purchased', 'select', [
            'label'  => __('Only those who purchased product can earn points'),
            'title'  => __('Only those who purchased product can earn points'),
            'name'   => 'is_purchased',
            'values' => ['1' => __('Yes'), '0' => __('No')]
        ]);
        $isEnableEmail = $fieldset->addField('is_enabled_email', 'select', [
            'label'  => __('Enable email sent to Customers for their birthdays'),
            'title'  => __('Enable email sent to Customers for their birthdays'),
            'name'   => 'is_enabled_email',
            'values' => ['1' => __('Yes'), '0' => __('No')]
        ]);
        $sender = $fieldset->addField('sender', 'select', [
            'label'  => __('Sender'),
            'title'  => __('Sender'),
            'name'   => 'sender',
            'values' => $this->identity->toOptionArray()
        ]);
        $emailTemplate = $fieldset->addField('email_template', 'select', [
            'label'  => __('Email template sent to customers for their birthdays'),
            'title'  => __('Email template sent to customers for their birthdays'),
            'name'   => 'email_template',
            'values' => $this->emailTemplate->setPath(self::DEFAULT_TEMPLATE_PATH)->toOptionArray()
        ]);
        $minInterval = $fieldset->addField('min_interval', 'text', [
            'label' => __('Minimum interval between Likes'),
            'title' => __('Minimum interval between Likes'),
            'name'  => 'min_interval',
            'class' => 'validate-digits',
            'note'  => __('(seconds). This is the minimum interval between 2 repeated interactions made by customers to get rewards')
        ]);

        if ($model->getRuleId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        $blockDependence = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');
        $actionsTabDependence = $this->fieldFactory->create(
            [
                'fieldData'   => [
                    'value'     => implode(',', [
                        CustomerEvents::SHARE_PURCHASE_FACEBOOK,
                        CustomerEvents::LIKE_PAGE_WITH_FACEBOOK,
                        CustomerEvents::TWEET_PAGE_WITH_TWITTER,
                        CustomerEvents::GOOGLE_PLUS,
                        CustomerEvents::PRODUCT_REVIEW
                    ]),
                    'separator' => ','
                ],
                'fieldPrefix' => ''
            ]
        );
        $blockDependence->addFieldMap($pointAction->getHtmlId(), $pointAction->getName())
            ->addFieldMap($isEnableEmail->getHtmlId(), $isEnableEmail->getName())
            ->addFieldMap($sender->getHtmlId(), $sender->getName())
            ->addFieldMap($emailTemplate->getHtmlId(), $emailTemplate->getName())
            ->addFieldDependence($isEnableEmail->getName(), $pointAction->getName(), CustomerEvents::CUSTOMER_BIRTHDAY)
            ->addFieldDependence($sender->getName(), $pointAction->getName(), CustomerEvents::CUSTOMER_BIRTHDAY)
            ->addFieldDependence($emailTemplate->getName(), $pointAction->getName(), CustomerEvents::CUSTOMER_BIRTHDAY)
            ->addFieldMap($minWords->getHtmlId(), $minWords->getName())
            ->addFieldMap($isPurchased->getHtmlId(), $isPurchased->getName())
            ->addFieldDependence($minWords->getName(), $pointAction->getName(), CustomerEvents::PRODUCT_REVIEW)
            ->addFieldDependence($isPurchased->getName(), $pointAction->getName(), CustomerEvents::PRODUCT_REVIEW)
            ->addFieldMap($fbAppId->getHtmlId(), $fbAppId->getName())
            ->addFieldDependence($fbAppId->getName(), $pointAction->getName(), CustomerEvents::SHARE_PURCHASE_FACEBOOK)
            ->addFieldMap($minInterval->getHtmlId(), $minInterval->getName())
            ->addFieldDependence(
                $minInterval->getName(),
                $pointAction->getName(),
                $this->fieldFactory->create(
                    [
                        'fieldData'   => [
                            'value'     => implode(',', [
                                CustomerEvents::TWEET_PAGE_WITH_TWITTER,
                                CustomerEvents::LIKE_PAGE_WITH_FACEBOOK,
                                CustomerEvents::GOOGLE_PLUS
                            ]),
                            'separator' => ','
                        ],
                        'fieldPrefix' => ''
                    ]
                )
            )->addFieldMap('rule_max_point_period', 'rule[max_point_period]')
            ->addFieldDependence('rule[max_point_period]', $pointAction->getName(), $actionsTabDependence)
            ->addFieldMap('rule_max_point', 'rule[max_point]')
            ->addFieldDependence('rule[max_point]', $pointAction->getName(), $actionsTabDependence);

        $this->setChild('form_after', $blockDependence);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
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
