<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

use Magento\Framework\Api\SearchCriteriaBuilder as Criteria;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\ObjectManagerInterface;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $queueRepository;

    protected $criteriaBuilder;

    protected $sortOrderBuilder;

    protected $objectManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \PSS\CRM\Api\QueueRepositoryInterface $queueRepository,
        Criteria $criteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        parent::__construct($context);
        $this->queueRepository = $queueRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute()
    {
        set_time_limit(0);
        $resultRedirect = $this->resultRedirectFactory->create();
        $selectedIds = $this->getRequest()->getParam('selected');
        $excludedIds = $this->getRequest()->getParam('excluded');
        $namespace = $this->getRequest()->getParam('namespace');

        if($selectedIds && is_array($selectedIds)){
            /**
             * Picked selected items
             */
            $filter = $this->criteriaBuilder
                ->addFilter('pss_crm_queue_id', $selectedIds, 'in')
                ->create();

        }else if ($excludedIds && is_array($excludedIds)){

            /**
             * All except exclude items
             */
            $filter = $this->criteriaBuilder
                ->addFilter('pss_crm_queue_id', $excludedIds, 'nin')
                ->create();

        }else{

            /**
             * All records to process
             */
            $filter = $this->criteriaBuilder->create();

        }

        $queueData = $this->queueRepository->getList($filter)->getItems();

        try {

            foreach ($queueData as $product) {
                $this->queueRepository->delete($product);
            }
            $this->messageManager->addSuccess(__('The process has been finished successfully, deleted %1 record(s).', count($queueData)));
            $resultRedirect->setPath('crm/queue/grid');
            return $resultRedirect;
        }
        catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $resultRedirect->setPath('crm/queue/grid');
        return $resultRedirect;
    }
}