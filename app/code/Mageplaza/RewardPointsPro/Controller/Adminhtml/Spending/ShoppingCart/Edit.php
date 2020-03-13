<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\RewardPointsPro\Controller\Adminhtml\Spending\ShoppingCart;

use Mageplaza\RewardPointsPro\Controller\Adminhtml\Spending\ShoppingCart;

/**
 * Class Edit
 * @package Mageplaza\RewardPointsPro\Controller\Adminhtml\Spending\ShoppingCart
 */
class Edit extends ShoppingCart
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $model = $this->shoppingCartSpendingRuleFactory->create();
        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $model->load($id);
            if ($id) {
                $model->load($id);
                if (!$model->getRuleId()) {
                    $this->messageManager->addError(__('This rule no longer exists.'));
                    $this->_redirect('*/*/');

                    return;
                }
            }
        }

        $model->getActions()->setFormName('sales_rule_form');
        $model->getActions()->setJsFormObject(
            $model->getActionsFieldSetId($model->getActions()->getFormName())
        );
        $this->registry->register('shopping_cart_spending_rule', $model);
        $resultPage->getConfig()->getTitle()->prepend(__('Shopping Cart Spending Rule'));

        return $resultPage;
    }
}
