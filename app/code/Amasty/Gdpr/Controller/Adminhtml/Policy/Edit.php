<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends AbstractPolicy
{
    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    public function __construct(
        Context $context,
        PolicyRepositoryInterface $policyRepository
    ) {
        parent::__construct($context);
        $this->policyRepository = $policyRepository;
    }

    /**
     * Edit action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $title = __('New Privacy Policy');

        if ($id) {
            try {
                $model = $this->policyRepository->getById($id);
                $title = __('Edit Privacy Policy %1', $model->getPolicyVersion());
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This policy no longer exists.'));
                return $this->_redirect('*/*/index');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if (!$id) {
            $resultPage->getLayout()->unsetElement('store_switcher');
        }

        $resultPage->setActiveMenu('Amasty_Gdpr::policy');
        $resultPage->addBreadcrumb(__('Privacy Policy'), __('Privacy Policy'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
