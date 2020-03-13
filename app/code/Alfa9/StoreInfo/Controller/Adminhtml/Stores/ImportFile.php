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
use Magento\Framework\File\Csv;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterfaceFactory;
use Alfa9\StoreInfo\Controller\Adminhtml\Stores;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Alfa9\StoreInfo\Model\Uploader;
use Alfa9\StoreInfo\Model\UploaderPool;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite as BaseUrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Alfa9\StoreInfo\Block\Stockists;

class ImportFile extends Stores
{
    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    public $uploaderPool;
    
    /**
     * @var csvProcessor
     */
    public $csvProcessor;


    /**
     * @var BaseUrlRewrite
     */
    protected $urlRewrite;

    /**
     * Url rewrite service
     *
     * @var $urlRewriteService
     */
    protected $urlRewriteService;

    /**
     * Url finder
     *
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /**
     * Configuration
     *
     * @var Stockists
     */
    protected $stockistsConfig;

    /**
     * StockistInterfaceFactory
     *
     * @var Stockists
     */
    protected $stockistFactory;

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
        BaseUrlRewrite $urlRewrite,
        UrlRewriteService $urlRewriteService,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        Stockists $stockistsConfig,
        UrlRewriteFactory $urlRewriteFactory,
        Csv $csvProcessor
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->urlRewrite = $urlRewrite;
        $this->urlRewriteService = $urlRewriteService;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->stockistsConfig = $stockistsConfig;
        $this->stockistFactory = $stockistFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->uploaderPool = $uploaderPool;
        $this->urlRewriteFactory = $urlRewriteFactory;
        parent::__construct($registry, $stockistRepository, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $stockist = null;
        $data = $this->getRequest()->getPostValue();
        $filePath = $data["import"][0]["path"].$data["import"][0]["file"];
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data["import"][0]["path"] && $data["import"][0]["file"]) {
            
            try {
                $rawStockistData = $this->csvProcessor->getData($filePath);
                
                // first row of file represents headers
                $fileHeaders = $rawStockistData[0];
                $processedStockistData = $this->filterFileData($fileHeaders, $rawStockistData);
            
                foreach($processedStockistData as $individualStockist) {
                    
                    $stockistId = !empty($individualStockist['storeinfo_id']) ? $individualStockist['storeinfo_id'] : null;

                    if ($stockistId) {
                        $stockist = $this->stockistRepository->getById((int)$stockistId);
                    } else {
                        unset($individualStockist['storeinfo_id']);
                        $stockist = $this->stockistFactory->create();
                    }
                    $storeIds = $individualStockist["store_id"] ? $this->storeManager->getStore()->getId() : 0;

                    $this->dataObjectHelper->populateWithArray($stockist,$individualStockist,StockistInterface::class);
                    $this->stockistRepository->save($stockist);

                    if($individualStockist["link"]){
                        $this->saveUrlRewrite($individualStockist["link"], $stockist->getId(), $storeIds);
                    }

                }
    
                $this->messageManager->addSuccessMessage(__('Your file has been imported successfully'));
                
                $resultRedirect->setPath('storeinfo/stores');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($stockist != null) {
                    $this->storeStockistDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $stockist,
                            StockistInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('storeinfo/stores/edit');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was an error importing the file'));
                if ($stockist != null) {
                    $this->storeStockistDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $stockist,
                            StockistInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('storeinfo/stores/import');
            }
                
        } else {
            $this->messageManager->addError(__('Please upload a file'));
        }

        return $resultRedirect;
    }

    /**
     * @param $stockistData
     */
    public function storeStockistDataToSession($stockistData)
    {
        $this->_getSession()->setLimesharpStockistsStoresData($stockistData);
    }

    /**
     * Filter data so that it will skip empty rows and headers
     *
     * @param   array $fileHeaders
     * @param   array $rawStockistData
     * @return  array
     */
    public function filterFileData(array $fileHeaders, array $rawStockistData)
    {
        $rowCount=0;
        $rawDataRows = [];
        
        foreach ($rawStockistData as $rowIndex => $dataRow) {
            
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rawStockistData[$rowIndex]);
                continue;
            }
            /* we take rows from [0] = > value to [website] = base */
            if ($rowIndex > 0) {
                foreach ($dataRow as $rowIndex => $dataRowNew) {
                    $rawDataRows[$rowCount][$fileHeaders[$rowIndex]] = $dataRowNew;
                }
            }
            $rowCount++;
        }
        return $rawDataRows;
    }
    
    
    /**
     * @param $type
     * @return Uploader
     * @throws \Exception
     */
    public function getUploader($type)
    {
        return $this->uploaderPool->getUploader($type);
    }

    /**
     * Saves the url rewrite for that specific store
     * @param $link string
     * @param $id int
     * @param $storeIds string
     * @return void
     */
    public function saveUrlRewrite($link, $id, $storeIds)
    {
        $moduleUrl = $this->stockistsConfig->getModuleUrlSettings();
        $getCustomUrlRewrite = $moduleUrl . "/" . $link;
        $stockistId = $moduleUrl . "-" . $id;

        $storeIds = explode(",", $storeIds);
        foreach ($storeIds as $storeId){

            $filterData = [
                UrlRewriteService::STORE_ID => $storeId,
                UrlRewriteService::REQUEST_PATH => $getCustomUrlRewrite,
                UrlRewriteService::ENTITY_ID => $id,

            ];

            // check if there is an entity with same url and same id
            $rewriteFinder = $this->urlFinder->findOneByData($filterData);

            // if there is then do nothing, otherwise proceed
            if ($rewriteFinder === null) {

                // check maybe there is an old url with this target path and delete it
                $filterDataOldUrl = [
                    UrlRewriteService::STORE_ID => $storeId,
                    UrlRewriteService::REQUEST_PATH => $getCustomUrlRewrite,
                ];
                $rewriteFinderOldUrl = $this->urlFinder->findOneByData($filterDataOldUrl);

                if ($rewriteFinderOldUrl !== null) {
                    $this->urlRewrite->load($rewriteFinderOldUrl->getUrlRewriteId())->delete();
                }

                // check maybe there is an old id with different url, in this case load the id and update the url
                $filterDataOldId = [
                    UrlRewriteService::STORE_ID => $storeId,
                    UrlRewriteService::ENTITY_TYPE => $stockistId,
                    UrlRewriteService::ENTITY_ID => $id
                ];
                $rewriteFinderOldId = $this->urlFinder->findOneByData($filterDataOldId);

                if ($rewriteFinderOldId !== null) {
                    $this->urlRewriteFactory->create()->load($rewriteFinderOldId->getUrlRewriteId())
                        ->setRequestPath($getCustomUrlRewrite)
                        ->save();

                    continue;
                }

                // now we can save
                $this->urlRewriteFactory->create()
                    ->setStoreId($storeId)
                    ->setIdPath(rand(1, 100000))
                    ->setRequestPath($getCustomUrlRewrite)
                    ->setTargetPath("storeinfo/view/index")
                    ->setEntityType($stockistId)
                    ->setEntityId($id)
                    ->setIsAutogenerated(0)
                    ->save();
            }
        }
    }
    
}
