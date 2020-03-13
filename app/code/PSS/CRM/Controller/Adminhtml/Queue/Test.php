<?php
/**
 * @author Juan Carlos M. <juancarlos.martinez@alfa9.com>
 * @copyright Copyright (c) 2018 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Controller\Adminhtml\Queue;

use Magento\Framework\App\Action\Action;
use \Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder as Criteria;
use Magento\Framework\Api\SortOrderBuilder;
use Alfa9\MDM\Helper\Data;
use PSS\CRM\Api\QueueRepositoryInterface;

error_reporting(-1);
set_time_limit(0);

class Test extends Action
{
    protected $_logger;
    protected $_scopeConfig;
    protected $_queueRepository;
    protected $_criteriaBuilder;
    protected $_sortOrderBuilder;
    protected $_objectManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        QueueRepositoryInterface $queueRepository,
        Criteria $criteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ){
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueRepository = $queueRepository;
        $this->_criteriaBuilder = $criteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        parent::__construct($context);
    }

    public function execute()
    {



    }
}