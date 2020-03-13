<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterfaceFactory;
use Alfa9\StoreInfo\Controller\Adminhtml\Stores;
use Alfa9\StoreInfo\Model\Uploader;
use Alfa9\StoreInfo\Model\UploaderPool;
use Alfa9\StoreInfo\Model\ResourceModel\Stores\CollectionFactory;

class Export extends Stores
{
    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    public $uploaderPool;
    
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**

     */
    public function __construct(
        Registry $registry,
        StockistRepositoryInterface $stockistRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        StockistInterfaceFactory $stockistFactory,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->stockistFactory = $stockistFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->uploaderPool = $uploaderPool;
        parent::__construct($registry, $stockistRepository, $resultPageFactory, $dateFilter, $context);
    }
    
    /**
     * Export data grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        try {
            
            $content = '';
            $content .= '"store_id",';
            $content .= '"name",';
            $content .= '"address",';
            $content .= '"city",';
            $content .= '"country",';
            $content .= '"postcode",';
            $content .= '"region",';
            $content .= '"email",';
            $content .= '"phone",';
            $content .= '"link",';
            $content .= '"image",';
            $content .= '"latitude",';
            $content .= '"longitude",';
            $content .= '"status",';
            $content .= '"updated_at",';
            $content .= '"created_at",';
            $content .= '"schedule",';
            $content .= '"station",';
            $content .= '"description",';
            $content .= '"intro",';
            $content .= '"details_image",';
            $content .= '"distance",';
            $content .= '"external_link"';
            $content .= "\n";

            $fileName = 'stockists_export.csv';
            $collection = $this->collectionFactory->create()->getData();
            
            foreach ($collection as $stockist) {
                array_shift($stockist); //skip the id
                $content .= implode(",", array_map([$this, 'addQuotationMarks'],$stockist));
                $content .= "\n";
            }

            return $this->fileFactory->create(
                $fileName,
                $content,
                DirectoryList::VAR_DIR
            );
            
            $this->messageManager->addSuccessMessage(__('You exported the file. It can be found in var folder or in browser downloads.'));
            $resultRedirect->setPath('storeinfo/stores');
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem exporting the data'));
            $resultRedirect->setPath('storeinfo/stores/export');
        }
        
        return $resultRedirect;

    }
    
     /**
     * Add quotes to fields
     * @param string
     * @return string
     */
    public function addQuotationMarks($row)
    {
        return sprintf('"%s"', $row);
    }
}
