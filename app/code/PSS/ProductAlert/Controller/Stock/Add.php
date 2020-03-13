<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Controller\Stock;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Add
 * @package PSS\ProductAlert\Controller\Stock
 */
class Add extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    protected $stockFactory;
    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    protected $stockCollectionFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;
    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ProductAlert\Model\StockFactory $stockFactory
     * @param \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\StockFactory $stockFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        $backUrl = $this->getRequest()->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $data = $this->getRequest()->getParams();
        $productId = (int)$this->getRequest()->getParam('product_id');
        $guestEmail = $this->getRequest()->getParam('email');
        $parentId = (int)$this->getRequest()->getParam('parent_id') ?: $productId;
        $redirect = $this->redirectFactory->create();

        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $product = $this->productRepository->getById($productId);
            $storeId = $this->storeManager->getStore()->getId();
        }catch (NoSuchEntityException $exception) {
            $websiteId = 0;
            $storeId = 0;
            $product = null;
        }

        if($product) {
            /** @var \Magento\ProductAlert\Model\Stock $model */
            $model = $this->stockFactory->create();
            $model->setProductId($product->getId())
                ->setData('store_id', $storeId)
                ->setWebsiteId($websiteId);
                //->setParentId($parentId);
            /** @var \Magento\ProductAlert\Model\ResourceModel\Stock\Collection $collection */
            $collection = $this->stockCollectionFactory->create()
                ->addWebsiteFilter($websiteId)
                ->addFieldToFilter('product_id', $productId)
                ->addStatusFilter(0)
                ->setCustomerOrder();
            $guestEmail = filter_var($guestEmail, FILTER_SANITIZE_EMAIL);
            $validEmail = true;
            try {
                if (!\Zend_Validate::is($guestEmail, 'EmailAddress')) {
                    $validEmail =  false;
                }
            }catch (\Zend_Validate_Exception $exception) {
                $validEmail =  false;
            }
            if($validEmail) {
                try {
                    $customer = $this->customerRepository->get($guestEmail, $websiteId);
                    $model->setCustomerId($customer->getId());
                    $collection->addFieldToFilter('customer_id', $customer->getId());
                } catch (LocalizedException $exception) {
                    $model->setData('email', $guestEmail);
                    $collection->addFieldToFilter('email', $guestEmail);
                }
                if ($collection->getSize() > 0) {
                    $this->messageManager->addSuccessMessage(__('Thank you! You are already subscribed to this product.'));
                } else {
                    try  {
                        $model->save();
                        $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
                    }catch (\Exception $exception) {
                        $this->messageManager->addErrorMessage(_("An unknown error has occurred"));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__("Invalid Email."));
            }
        } else {
            $this->messageManager->addErrorMessage(__("Invalid product."));
        }
        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }
}