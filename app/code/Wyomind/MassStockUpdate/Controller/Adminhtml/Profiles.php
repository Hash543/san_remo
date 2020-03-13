<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml;

abstract class Profiles extends \Wyomind\MassStockUpdate\Controller\Adminhtml\AbstractController
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Helper\Config
     */
    protected $_configHelper = null;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|null
     */
    protected $_directoryRead = null;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;
    /**
     * @var null
     */
    protected $_parserHelper = null;
    /**
     * @var \Magento\Framework\App\CacheInterface|null
     */
    protected $_cacheManager = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    protected $_storeManager = null;
    /**
     * @var null|\Wyomind\Core\Helper\Data
     */
    protected $_coreHelper = null;

    /**
     * Profiles constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Wyomind\MassStockUpdate\Logger\Logger $logger
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Model\Context $contextModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wyomind\MassStockUpdate\Helper\Config $configHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Wyomind\Core\Helper\Data $coreHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context, \Wyomind\MassStockUpdate\Logger\Logger $logger,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Model\Context $contextModel,
        \Magento\Framework\Registry $coreRegistry,
        \Wyomind\MassStockUpdate\Helper\Config $configHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Wyomind\Core\Helper\Data $coreHelper)
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_configHelper = $configHelper;
        $this->_cacheManager = $contextModel->getCacheManager();
        $this->_directoryRead = $directoryRead->create("");
        $this->_directoryList = $directoryList;
        $this->_storeManager = $storeManager;
        $this->_coreHelper = $coreHelper;

        parent::__construct($context, $logger, $resultForwardFactory, $resultRawFactory, $resultPageFactory);
    }


}
