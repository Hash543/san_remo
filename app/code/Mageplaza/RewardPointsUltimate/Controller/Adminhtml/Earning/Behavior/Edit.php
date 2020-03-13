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

namespace Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Earning\Behavior;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;

/**
 * Class Edit
 * @package Mageplaza\RewardPointssultimate\Controller\Adminhtml\Earning\Behavior
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param BehaviorFactory $behaviorFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BehaviorFactory $behaviorFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->behaviorFactory = $behaviorFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $model = $this->behaviorFactory->create();
        if ($this->getRequest()->getParam('rule_id')) {
            $model->load($this->getRequest()->getParam('rule_id'));
        }
        $this->registry->register('behavior_earning_rule', $model);
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Behavior Rule'));

        return $resultPage;
    }
}
