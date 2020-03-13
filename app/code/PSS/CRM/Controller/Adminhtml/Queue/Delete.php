<?php
/**
 * @copyright (c) 2018. Alfa9 (http://www.alfa9.com)
 * @author Xavier Sanz <xsanz@alfa9.com>
 * @package iese_publishing
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

use Magento\Framework\Api\SearchCriteriaBuilder as Criteria;

/**
 * Class Delete
 * @package PSS\CRM\Controller\Adminhtml\Queue
 */

class Delete extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $queueRepositoryInterface;

    protected $queueInterface;

    protected $criteriaBuilder;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \PSS\CRM\Api\QueueRepositoryInterface $queueRepositoryInterface
     * @param Criteria $criteriaBuilder
     */

     public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PSS\CRM\Api\QueueRepositoryInterface $queueRepositoryInterface,
        Criteria $criteriaBuilder
    ) {
        parent::__construct($context);
        $this->queueRepositoryInterface = $queueRepositoryInterface;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $itemId = $this->getRequest()->getParam('id');
            if ($itemId) {
                $this->queueRepositoryInterface->deleteById($itemId);
                $this->messageManager->addSuccess(__('Queue Item has been deleted successfully.'));
            } else {
                $this->messageManager->addError(__('Queue Item Id is null.'));
            }
        }
        catch (\Exception $e) {
            $this->messageManager->addError(__('An error ocurred while deleting the Item ERR CODE: ' . $e->getMessage()));
        }

        $resultRedirect->setPath('*/*/grid');

        return $resultRedirect;
    }
}