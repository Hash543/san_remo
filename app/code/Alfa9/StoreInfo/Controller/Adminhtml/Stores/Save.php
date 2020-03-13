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
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterfaceFactory;
use Alfa9\StoreInfo\Controller\Adminhtml\Stores;
use Alfa9\StoreInfo\Model\Uploader;
use Alfa9\StoreInfo\Model\UploaderPool;
use Magento\UrlRewrite\Model\UrlRewrite as BaseUrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Alfa9\StoreInfo\Block\Stockists;

class Save extends Stores
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

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /**
     * @param Registry $registry
     * @param StockistRepositoryInterface $stockistRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param StockistInterfaceFactory $stockistFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param UploaderPool $uploaderPool
     */
    public function __construct(
        Registry $registry,
        StockistRepositoryInterface $stockistRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        BaseUrlRewrite $urlRewrite,
        UrlRewriteService $urlRewriteService,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StockistInterfaceFactory $stockistFactory,
        Stockists $stockistsConfig,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool
    ) {
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
        /** @var \Alfa9\StoreInfo\Api\Data\StockistInterface $stockist */
        $stockist = null;
        $data = $this->getRequest()->getPostValue();
        $id = !empty($data['storeinfo_id']) ? $data['storeinfo_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();


        try {
            if ($id) {
                $stockist = $this->stockistRepository->getById((int)$id);
            } else {
                unset($data['storeinfo_id']);
                $stockist = $this->stockistFactory->create();
            }

            // var_dump($data); die;
            $image = $this->getUploader('image')->uploadFileAndGetName('image', $data);
            $data['image'] = $image;
            $image2 = $this->getUploader('image')->uploadFileAndGetName('image2', $data);
            $data['image2'] = $image2;
            $image3 = $this->getUploader('image')->uploadFileAndGetName('image3', $data);
            $data['image3'] = $image3;
            /*
            $details_image = $this->getUploader('image')->uploadFileAndGetName('details_image', $data);
            $data['details_image'] = $details_image;
            */

            if(!empty($data['store_id']) && is_array($data['store_id'])) {
                if(in_array('0',$data['store_id'])){
                    $data['store_id'] = '0';
                }
                else{
                    $data['store_id'] = implode(",", $data['store_id']);
                }
            }
            $storeId = $data["store_id"] ? $this->storeManager->getStore()->getId() : 0;

            $this->dataObjectHelper->populateWithArray($stockist, $data, StockistInterface::class);
            $this->stockistRepository->save($stockist);

            if($data["link"]) {
                $this->saveUrlRewrite($data["link"], $stockist->getId(), $storeId);
            }

            $this->messageManager->addSuccessMessage(__('You saved the store'));
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('storeinfo/stores/edit', ['storeinfo_id' => $stockist->getId()]);
            } else {
                $resultRedirect->setPath('storeinfo/stores');
            }
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
            $resultRedirect->setPath('storeinfo/stores/edit', ['storeinfo_id' => $id]);
        } catch (\Exception $e) {
            var_dump($e->getMessage()); die;
            $this->messageManager->addErrorMessage(__('There was a problem saving the store'));
            if ($stockist != null) {
                $this->storeStockistDataToSession(
                    $this->dataObjectProcessor->buildOutputDataArray(
                        $stockist,
                        StockistInterface::class
                    )
                );
            }
            $resultRedirect->setPath('storeinfo/stores/edit', ['store_id' => $id]);
        }
        return $resultRedirect;
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
     * @param $stockistData
     */
    public function storeStockistDataToSession($stockistData)
    {
        $this->_getSession()->setLimesharpStockistsStoresData($stockistData);
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
