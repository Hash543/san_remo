<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterfaceFactory;
use Alfa9\StoreInfo\Controller\Adminhtml\Stores as StockistController;
use Alfa9\StoreInfo\Model\Stores;
use Alfa9\StoreInfo\Model\ResourceModel\Stores as StockistResourceModel;

class InlineEdit extends StockistController
{
    /**
     * @var DataObjectHelper
     */
    public $dataObjectHelper;
    /**
     * @var DataObjectProcessor
     */
    public $dataObjectProcessor;
    /**
     * @var JsonFactory
     */
    public $jsonFactory;
    /**
     * @var StockistResourceModel
     */
    public $stockistResourceModel;

    /**
     * @param Registry $registry
     * @param StockistRepositoryInterface $stockistRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param JsonFactory $jsonFactory
     * @param StockistResourceModel $stockistResourceModel
     */
    public function __construct(
        Registry $registry,
        StockistRepositoryInterface $stockistRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        JsonFactory $jsonFactory,
        StockistResourceModel $stockistResourceModel
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->jsonFactory         = $jsonFactory;
        $this->stockistResourceModel = $stockistResourceModel;
        parent::__construct($registry, $stockistRepository, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $stockistId) {
            /** @var \Alfa9\StoreInfo\Model\Stores|StockistInterface $stockist */
            $stockist = $this->stockistRepository->getById((int)$stockistId);
            try {
                $stockistData = $this->filterData($postItems[$stockistId]);
                $this->dataObjectHelper->populateWithArray($stockist, $stockistData , StockistInterface::class);
                $this->stockistResourceModel->saveAttribute($stockist, array_keys($stockistData));
            } catch (LocalizedException $e) {
                $messages[] = $this->getErrorWithStockistId($stockist, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithStockistId($stockist, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithStockistId(
                    $stockist,
                    __('Something went wrong while saving the stockist.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add stockist id to error message
     *
     * @param Stores $stockist
     * @param string $errorText
     * @return string
     */
    public function getErrorWithStockistId(Stores $stockist, $errorText)
    {
        return '[Stockist ID: ' . $stockist->getId() . '] ' . $errorText;
    }
}
