<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Preview extends \Wyomind\MassStockUpdate\Controller\Adminhtml\AbstractController
{

    protected $_profileModelFactory = null;
    protected $_dataHelper = null;
    protected $_configHelper = null;
    protected $_storageHelper = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context, \Wyomind\MassStockUpdate\Logger\Logger $logger,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\MassStockUpdate\Model\ProfilesFactory $profileModelFactory,
        \Wyomind\MassStockUpdate\Helper\Data $dataHelper,
        \Wyomind\MassStockUpdate\Helper\Storage $storageHelper,
        \Wyomind\MassStockUpdate\Helper\Config $configHelper
    )
    {
        parent::__construct($context, $logger, $resultForwardFactory, $resultRawFactory, $resultPageFactory);
        $this->_profileModelFactory = $profileModelFactory;
        $this->_dataHelper = $dataHelper;
        $this->_storageHelper = $storageHelper;
        $this->_configHelper = $configHelper;
    }

    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $request = $this->getRequest();
            $isOutput = $this->getRequest()->getParam("isOutput");
            $model = $this->_profileModelFactory->create()->load($id);
            $file = $this->_storageHelper->evalRegexp($request->getParam("file_path"),$request->getParam("file_system_type"));
            $request->setParam("file_path", $file);
            $previewDta = $model->getImportData($request, $this->_configHelper->getSettingsNbPreview(), $isOutput);

            return $this->getResponse()->representJson(json_encode($previewDta));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getResponse()->representJson('{"error":"true","message":"' . preg_replace("/\r|\n|\t|\\\\/", "", nl2br(htmlentities($e->getMessage()))) . '"}');
        }
    }

}
