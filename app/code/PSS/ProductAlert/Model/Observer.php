<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Model;
/**
 * Class Observer
 * @package PSS\ProductAlert\Model
 */
class Observer extends \Magento\ProductAlert\Model\Observer {
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Observer constructor.
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceColFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockColFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\ProductAlert\Model\EmailFactory $emailFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockColFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\ProductAlert\Model\EmailFactory $emailFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        parent::__construct(
            $catalogData,
            $scopeConfig,
            $storeManager,
            $priceColFactory,
            $customerRepository,
            $productRepository,
            $dateFactory,
            $stockColFactory,
            $transportBuilder,
            $emailFactory,
            $inlineTranslation
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _processStock(\Magento\ProductAlert\Model\Email $email)
    {
        $email->setType('stock');
        try {
            $collection = $this->_stockColFactory->create()
            ->addStatusFilter(
                0
            )->setCustomerOrder();
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return $this;
        }
        $prevCustomerEmail = null;
        $prevCustomerId = null;
        /** @var \Magento\ProductAlert\Model\Stock $alert */
        foreach ($collection as $alert) {
            try {
                $websiteId = $alert->getWebsiteId();
                /** @var \Magento\Store\Model\Website $website */
                $website = $this->_storeManager->getWebsite($websiteId);
                $storeId = $alert->getData('store_id') ? $alert->getData('store_id') : $website->getDefaultStore()->getId();
                $email->setWebsite($website);

                if (!$this->_scopeConfig->getValue(
                    self::XML_PATH_STOCK_ALLOW,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $website->getDefaultGroup()->getDefaultStore()->getId()
                )
                ) {
                    continue;
                }

                $customer = $this->getCustomerFromAlert($alert, $websiteId);
                if ($customer->getEmail() !== $prevCustomerEmail) {
                    if ($prevCustomerEmail) {
                        $this->registry->unregister('pss_notify_data');
                        $this->registry->register(
                            'pss_notify_data',
                            [
                                'guest' => $this->checkIfUserIsGuest($prevCustomerEmail),
                                'email' => $prevCustomerEmail
                            ]
                        );
                        $email->send();
                    }
                    $email->clean();
                    $email->setCustomerData($customer);
                }
                $prevCustomerEmail = $customer->getEmail();
                $prevCustomerId = $alert->getCustomerId();
                /** @var \Magento\Catalog\Model\Product $product */
                try {
                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $storeId
                    );
                } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
                    continue;
                }

                if ($product->isSalable()) {
                    $email->addStockProduct($product);

                    $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                    $alert->setSendCount($alert->getSendCount() + 1);
                    $alert->setStatus(1);
                    $alert->save();
                }
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                continue;
            }
        }
        if ($prevCustomerEmail) {
            $this->registry->unregister('pss_notify_data');
            $this->registry->register(
                'pss_notify_data',
                [
                    'guest' => $this->checkIfUserIsGuest($prevCustomerEmail),
                    'email' => $prevCustomerEmail
                ]
            );
            try {
                $email->send();
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
            }
        }

        return $this;
    }

    /**
     * @param $email
     * @return bool
     */
    public function checkIfUserIsGuest($email) {
        try {
            $customer = $this->customerRepository->get($email);
            if($customer && $customer->getId()) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }
    }
    /**
     * @param \Magento\ProductAlert\Model\Stock $alert
     * @param $websiteId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerFromAlert($alert, $websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->_storeManager->getStore()->getWebsite()->getId();
        }

        if ($alert->getCustomerId()) {
            $customer = $this->customerRepository->getById(
                $alert->getCustomerId()
            );
        } else {

            try {
                $customer = $this->customerRepository->get(
                    $alert->getData('email'),
                    $websiteId
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $customer = $this->customerFactory->create()->getDataModel();
                $customer->setWebsiteId(
                    $websiteId
                )->setEmail(
                    $alert->getData('email')
                )->setLastname(
                    ''
                )->setGroupId(
                    0
                )->setId(
                    0
                );
            }
        }
        $customer->setStoreId($alert->getData('store_id'));
        return $customer;
    }
}