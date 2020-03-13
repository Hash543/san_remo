<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rgrid
 */

namespace Amasty\Rgrid\Controller\Adminhtml\Promo\Quote;

class Duplicate extends \Amasty\Rgrid\Controller\Adminhtml\Promo\Quote
{
    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    public $ruleRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
    ) {
        parent::__construct($context, $collectionFactory);
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
    */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $rule = $this->ruleRepository->getById($id);
                $rule->setRuleId(null);
                $rule = $this->ruleRepository->save($rule);
                $this->messageManager->addSuccessMessage(__('The rule has been duplicated.'));
                $this->_redirect('sales_rule/*/edit', ['id' => $rule->getRuleId()]);
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t duplicate the rule right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('sales_rule/*/');
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        $this->_redirect('sales_rule/*/');
    }
}
