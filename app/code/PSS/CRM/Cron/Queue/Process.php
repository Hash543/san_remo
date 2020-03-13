<?php
/**
 * @author Juan Carlos M. <juancarlos.martinez@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Cron\Queue;

use PSS\CRM\Logger\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder as Criteria;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\ObjectManagerInterface;
use PSS\CRM\Api\QueueRepositoryInterface;

class Process {

    protected $_logger;
    protected $_scopeConfig;
    protected $_queueRepository;
    protected $_criteriaBuilder;
    protected $_sortOrderBuilder;
    protected $_objectManager;

    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
        QueueRepositoryInterface $queueRepository,
        Criteria $criteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ){
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_queueRepository = $queueRepository;
        $this->_criteriaBuilder = $criteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
    }

    public function execute()
    {

        error_reporting(-1);
        set_time_limit(0);


        // PROCCESS CUSTOMER ITEMS FIRST

        $filter = $this->_criteriaBuilder
            ->addFilter('process_status', '1', 'neq')
            ->create();

        $queueData = $this->_queueRepository->getList($filter)->getItems();

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
                        $this->_logger->error("There's has been an error processing cron: " . $e->getMessage());
                    }
                }
            }

            //$this->reindexAll();
            //$this->messageManager->addSuccess(__('The process has finished, processed %1 record(s).', count($queueData)));

        }
        catch (\Exception $e) {
            $this->_logger->error("There's has been an error processing cron: " . $e->getMessage());
        }

        return $this;

    }

}
