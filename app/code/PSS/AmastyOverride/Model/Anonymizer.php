<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace PSS\AmastyOverride\Model;

use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\CustomerData;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Sales\Model\ResourceModel\GridPool;

class Anonymizer extends \Amasty\Gdpr\Model\Anonymizer
{
    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    private $subscriberResource;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriber;

    private $isDeleting = false;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var ActionLogger
     */
    private $logger;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $nameGeneration;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    private $customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var GridPool
     */
    private $gridPool;

    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        AddressRepositoryInterface $addressRepository,
        \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberResource,
        TransportBuilder $transportBuilder,
        CollectionFactory $deleteRequestCollectionFactory,
        ActionLogger $logger,
        SenderResolverInterface $senderResolver,
        CustomerNameGenerationInterface $nameGeneration,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerInterfaceFactory $customerDataFactory,
        Config $configProvider,
        GridPool $gridPool
    ) {
        $this->eventManager = $eventManager;
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerData = $customerData;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->subscriber = $subscriber;
        $this->addressRepository = $addressRepository;
        $this->subscriberResource = $subscriberResource;
        $this->transportBuilder = $transportBuilder;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->logger = $logger;
        $this->senderResolver = $senderResolver;
        $this->nameGeneration = $nameGeneration;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerMapper = $customerMapper;
        $this->customerDataFactory = $customerDataFactory;
        $this->configProvider = $configProvider;
        $this->gridPool = $gridPool;

        parent::__construct($eventManager, $customerRepository, $orderCollectionFactory, $quoteCollectionFactory, $customerCollectionFactory,
            $customerData, $orderRepository, $quoteRepository, $subscriber, $addressRepository, $subscriberResource,
            $transportBuilder, $deleteRequestCollectionFactory, $logger, $senderResolver, $nameGeneration, $dataObjectHelper, $customerMapper, $customerDataFactory, $configProvider, $gridPool);
    }

    /**
     * @param OrderAddress|QuoteAddress|OrderAddressInterface|CustomerAddressInterface|null $address
     */
    private function anonymizeAddress($address)
    {
        $attributeCodes = $this->customerData->getAttributeCodes('customer_address');

        foreach ($attributeCodes as $code) {
            switch ($code) {
                case 'telephone':
                    $randomString = '000000000';
                        break;
                case 'fax':
                    $randomString = '0000000';
                    break;
                case 'country_id':
                case 'region':
                    continue 2;
                default:
                    $randomString = $this->generateFieldValue();
            }
            $address->setData($code, $randomString);
        }
    }

    /**
     * @param int|string $customerId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function anonymizeAccountInformation($customerId)
    {
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $this->customerRepository->getById($customerId);
        $oldEmail = $customer->getEmail();
        $customerName = $this->nameGeneration->getCustomerName($customer);

        $attributeCodes = $this->customerData->getAttributeCodes('customer');

        foreach ($attributeCodes as $attributeCode) {
            switch ($attributeCode) {
                case 'email':
                    $randomString = $this->getRandomEmail();
                    break;
                case 'dob':
                    $randomString = '1970-01-01';
                    break;
                case 'gender':
                    $randomString = 3; // Not Specified
                    break;
                case 'taxvat':
                    $randomString = '00000000T'; // Valid NIF
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }
            $customer->setData($attributeCode, $randomString);
        }

        if (!$this->isDeleting) {
            $this->sendConfirmationEmail($this::CONFIG_PATH_KEY_ANONYMISATION, $oldEmail, $customerName, $customer);
            $this->deleteRequestCollectionFactory->create()->deleteByCustomerId($customer->getId());
        } else {
            $this->sendConfirmationEmail($this::CONFIG_PATH_KEY_DELETION, $oldEmail, $customerName, $customer);
        }

        $this->customerRepository->save($customer);

        $addresses = $customer->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        foreach ($addresses as $address) {
            $this->anonymizeAddress($address);
            //@codingStandardsIgnoreStart
            $this->addressRepository->save($address);
            //@codingStandardsIgnoreEnd
        }

        $this->anonymizeThirdPartyInformation($customerId);
    }
}
