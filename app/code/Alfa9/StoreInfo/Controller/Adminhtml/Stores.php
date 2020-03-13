<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;

abstract class Stores extends Action
{
    /**
     * @var string
     */
    const ACTION_RESOURCE = 'Alfa9_StoreInfo::stores';
    /**
     * stockist factory
     *
     * @var StockistRepositoryInterface
     */
    public $stockistRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    public $dateFilter;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @param Registry $registry
     * @param StockistRepositoryInterface $stockistRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        StockistRepositoryInterface $stockistRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context

    ) {
        $this->coreRegistry      = $registry;
        $this->stockistRepository  = $stockistRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateFilter        = $dateFilter;
        parent::__construct($context);
    }

    /**
     * filter dates
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        
        return $data;
    }

}
