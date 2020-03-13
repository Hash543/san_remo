<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

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

class Anonymizer
{
    const ANONYMOUS_SYMBOL = '-';

    const RANDOM_LENGTH = 5;

    const EMAIL_TEMPLATE_CODE = 'amgdpr_anonimization';

    const CONFIG_PATH_KEY_ANONYMISATION = 'anonymisation';

    const CONFIG_PATH_KEY_DELETION = 'deletion';

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
    }

    /**
     * @param string|int $customerId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteCustomer($customerId)
    {
        $this->isDeleting = true;
        $this->anonymizeCustomer($customerId);

        $this->logger->logAction('delete_request_approved', $customerId);
    }

    /**
     * @param $customerId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function anonymizeCustomer($customerId)
    {
        $this->eventManager->dispatch(
            'before_amgdpr_customer_anonymisation',
            ['customerId' => $customerId, 'isDeleting' => $this->isDeleting]
        );

        $this->anonymizeOrders($customerId);
        $this->anonymizeQuotes($customerId);
        $this->deleteSubscription($customerId);
        $this->anonymizeAccountInformation($customerId);

        if (!$this->isDeleting) {
            $this->logger->logAction('data_anonymised_by_customer', $customerId);
        }

        $this->eventManager->dispatch(
            'after_amgdpr_customer_anonymisation',
            ['customerId' => $customerId, 'isDeleting' => $this->isDeleting]
        );
    }

    /**
     * @param int|string $customerId
     */
    private function anonymizeOrders($customerId)
    {
        /** @var OrderCollection $entities */
        $orders = $this->orderCollectionFactory->create();

        $orders->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId);

        /** @var \Magento\Sales\Model\Order $item */
        foreach ($orders as $item) {
            $this->prepareSalesData($item);
            //@codingStandardsIgnoreStart
            $this->orderRepository->save($item);
            //@codingStandardsIgnoreEnd
            $this->gridPool->refreshByOrderId($item->getId());
        }
    }

    /**
     * @param $incrementId
     * @return bool
     */
    public function anonymizeOrderByIncrementId($incrementId)
    {
        /** @var OrderCollection $entities */
        $orders = $this->orderCollectionFactory->create();

        $item = $orders->addFieldToSelect('*')
            ->addFieldToFilter(OrderInterface::INCREMENT_ID, $incrementId)->getFirstItem();

        if ($this->configProvider->isAvoidAnonymization()) {
            $orderStatuses = explode(',', $this->configProvider->getOrderStatuses());

            if (in_array($item->getStatus(), $orderStatuses)) {
                return false;
            }
        }

        $this->prepareSalesData($item);
        $this->orderRepository->save($item);
        $this->gridPool->refreshByOrderId($item->getId());

        return true;
    }

    /**
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $object
     */
    private function prepareSalesData($object)
    {
        $object->setCustomerFirstname($this->generateFieldValue());
        $object->setCustomerMiddlename($this->generateFieldValue());
        $object->setCustomerLastname($this->generateFieldValue());
        $object->setCustomerEmail($this->getRandomEmail());
        if ($object->getBillingAddress()) {
            $this->anonymizeAddress($object->getBillingAddress());
        }
        if ($object->getShippingAddress()) {
            $this->anonymizeAddress($object->getShippingAddress());
        }
    }

    /**
     * @return string
     */
    public function generateFieldValue()
    {
        $rand = self::ANONYMOUS_SYMBOL;
        if (!$this->isDeleting) {
            $rand = 'anonymous' . $this->getRandomString();
        }

        return $rand;
    }

    /**
     * @return string
     */
    private function getRandomString()
    {
        return bin2hex(openssl_random_pseudo_bytes(self::RANDOM_LENGTH));
    }

    /**
     * @return string
     */
    public function getRandomEmail()
    {
        $email = self::ANONYMOUS_SYMBOL;
        if (!$this->isDeleting) {
            $email = $this->generateFieldValue();
        }
        $email = $email . '@' . $this->getRandomString() . '.com';

        if ($this->isEmailExists($email)) {
            $email = $this->getRandomEmail();
        }

        return $email;
    }

    public function isEmailExists($email)
    {
        $collection = $this->customerCollectionFactory->create();

        return (bool)$collection->addFieldToFilter('email', $email)->getSize();
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
     */
    private function anonymizeQuotes($customerId)
    {
        /** @var QuoteCollection $entities */
        $quotes = $this->quoteCollectionFactory->create();

        $quotes->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId);

        /** @var \Magento\Quote\Model\Quote $item */
        foreach ($quotes as $item) {
            $this->prepareSalesData($item);
            //@codingStandardsIgnoreStart
            $this->quoteRepository->save($item);
            //@codingStandardsIgnoreEnd
        }
    }

    /**
     * @param int|string $customerId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteSubscription($customerId)
    {
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriber->loadByCustomerId($customerId);
        if ($subscriber->getId()) {
            $subscriber->unsubscribe();
            $this->subscriberResource->delete($subscriber);
        }
    }

    /**
     * @param int|string $customerId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function anonymizeThirdPartyInformation($customerId)
    {
        $savedCustomerData = $this->customerRepository->getById($customerId);
        $customData = $this->customerMapper->toFlatArray($savedCustomerData);

        $attributeCodes = $this->customerData->getAttributeCodes('customer');
        $exclude = $this->customerData->getAttributeCodes('exclude');
        $exclude = array_merge($exclude, $attributeCodes);

        foreach ($customData as $attributeCode => $value) {
            if (!in_array($attributeCode, $exclude)) {
                $customData[$attributeCode] = $this->generateFieldValue();
            }
        }

        $customer = $this->customerDataFactory->create();
        $customData = array_merge(
            $this->customerMapper->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $this->customerRepository->save($customer);
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

    /**
     * @param string                                $configPath
     * @param string                                $realEmail
     * @param string                                $customerName
     * @param \Magento\Customer\Model\Data\Customer $customer
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendConfirmationEmail($configPath, $realEmail, $customerName, $customer)
    {
        $template = $this->configProvider->getConfirmationEmailTemplate($configPath);

        $sender = $this->configProvider->getConfirmationEmailSender($configPath);

        $replyTo = $this->configProvider->getConfirmationEmailReplyTo($configPath);
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $customer->getStoreId()
            ]
        )->setTemplateVars(
            [
                'anonymousEmail' => $customer->getEmail(),
                'customerName' => $customerName
            ]
        )->setFrom(
            $sender
        )->addTo(
            $realEmail,
            $customerName
        )->setReplyTo(
            $replyTo
        )->getTransport();

        $transport->sendMessage();
    }

    /**
     * Get data of customer active orders
     *
     * @param int|string $customerId
     *
     * @return array
     */
    public function getCustomerActiveOrders($customerId)
    {
        $ordersData = [];

        if ($this->configProvider->isAvoidAnonymization()) {
            $orderStatuses = $this->configProvider->getOrderStatuses();

            if ($orderStatuses) {
                $orders = $this->orderCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', ['in' => explode(',', $orderStatuses)]);

                $ordersData = $orders->getData();
            }

            return $ordersData;
        }
    }
}
