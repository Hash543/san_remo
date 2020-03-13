<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

use Magento\Framework\Api\SearchCriteriaBuilder as Criteria;
use Magento\Framework\Api\SortOrderBuilder;

class MassProcess extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $queueRepository;

    protected $criteriaBuilder;

    protected $sortOrderBuilder;

    protected $objectManager;

    protected $indexerFactory;


    /**
     * MassProcess constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \PSS\CRM\Api\QueueRepositoryInterface $queueRepository
     * @param Criteria $criteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
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

        set_time_limit(-1);
        error_reporting(-1);

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
            foreach ($queueData as $item) {
                if($item->getData('process_status')!='1') {
                    try {
                        $processObject = $this->objectManager->create(($item['model']));
                        $result = $processObject->retry(json_decode($item['data'], true));
                        if (is_array($result)) {
                            //ACTUALIZAMOS LA ENTRADA EN LA COLA
                            switch ($result['resultado']) {
                                case 1: //ERROR
                                    $item->setData('executed_at', null);
                                    $item->setData('process_status', '2');
                                    $item->setData('result', $result['resultadoDescripcion']);
                                    break;
                                case 0: //SUCCESS
                                    $item->setData('executed_at', null);
                                    $item->setData('process_status', '1');
                                    $item->setData('result', $result['resultadoDescripcion']);
                                    break;
                                default:
                                    break;
                            }
                            $item->save();
                        }
                    } catch(\Exception $e) {
                        $this->messageManager->addErrorMessage(__('The process has thrown errors for id #').$item->getData('pss_crm_queue_id').': '. $e->getMessage());
                        //$this->messageManager->addExceptionMessage($e);
                    }
                }
            }

            //$this->reindexAll();
            $this->messageManager->addSuccess(__('The process has finished, processed %1 record(s).', count($queueData)));
            $resultRedirect->setPath('crm/queue/grid');
            return $resultRedirect;
        }
        catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $resultRedirect->setPath('crm/queue/grid');
        return $resultRedirect;
    }

    public function reindexAll(){

        // feel free to delete indexers which not require reindexing
        foreach ([
                     'catalog_category_product',
                     'catalog_product_category',
                     'catalog_product_price',
                     'catalog_product_attribute',
                     'cataloginventory_stock',
                     'catalogrule_rule',
                     'catalogrule_product',
                     'catalogsearch_fulltext',

                 ] as $indexerId) {

            /*
            $this->indexerFactory->create()
                ->load($indexerId)
                ->reindexAll();
            */
            $indexer = $this->_objectManager->get('Magento\Framework\Indexer\IndexerRegistry')->get($indexerId);
            $indexer->reindexAll();

        }

    }

}