<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

use Magento\Framework\Api\SearchCriteriaBuilder;
use PSS\CRM\Api\QueueRepositoryInterface;

class Process extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $queueRepository;


    /**
     * Process constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param QueueRepositoryInterface $queueRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        QueueRepositoryInterface $queueRepository
    ) {
        $this->queueRepository = $queueRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $item = $this->queueRepository->getById($this->getRequest()->getParam('id'));
        if ($item->getData('model')) {
            try {
                $processObject = $this->_objectManager->create($item->getData('model'));
                $result = $processObject->retry(json_decode($item->getData('data'), true));
                if(is_array($result)) {
                    //ACTUALIZAMOS LA ENTRADA EN LA COLA
                    switch($result['resultado'])
                    {
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
        $this->messageManager->addSuccess(__('The process has finished.'));
        $resultRedirect->setPath('crm/queue/grid');
        return $resultRedirect;
    }
}