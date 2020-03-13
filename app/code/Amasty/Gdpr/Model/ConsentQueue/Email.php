<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ConsentQueue;

use Amasty\Gdpr\Api\ConsentQueueRepositoryInterface;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\ConsentQueue;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\CollectionFactory;
use Amasty\Gdpr\Model\PolicyRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email
{
    const LOCK_FILE = 'amasty_gdpr_send_emails.lock';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsentQueueRepositoryInterface
     */
    private $consentQueueRepository;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $nameGeneration;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PolicyRepository
     */
    private $policyRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        ConsentQueueRepositoryInterface $consentQueueRepository,
        Filesystem $filesystem,
        Config $config,
        CustomerNameGenerationInterface $nameGeneration,
        EncryptorInterface $encryptor,
        DeploymentConfig $deploymentConfig,
        SenderResolverInterface $senderResolver,
        StoreManagerInterface $storeManager,
        PolicyRepository $policyRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->consentQueueRepository = $consentQueueRepository;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->nameGeneration = $nameGeneration;
        $this->encryptor = $encryptor;
        $this->deploymentConfig = $deploymentConfig;
        $this->senderResolver = $senderResolver;
        $this->storeManager = $storeManager;
        $this->policyRepository = $policyRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmails()
    {
        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        /** @var WriteInterface $lockFile */
        $lockFile = $directoryWrite->openFile(self::LOCK_FILE);
        try {
            $lockFile->lock(LOCK_EX | LOCK_NB);
        } catch (\Exception $exception) {
            return;
        }

        /** @var \Amasty\Gdpr\Model\ResourceModel\ConsentQueue\Collection $consentQueueCollection */
        $consentQueueCollection = $this->collectionFactory->create();
        $consentQueueCollection->addStatusFilter(ConsentQueue::STATUS_PENDING);
        foreach ($consentQueueCollection->getItems() as $consentEntity) {
            $this->sendEmail($consentEntity);
        }

        $lockFile->unlock();
    }

    /**
     * @param \Amasty\Gdpr\Model\ConsentQueue $consentEntity
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmail($consentEntity)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById($consentEntity->getCustomerId());
        $storeId = $customer->getStoreId();
        $customerName = $this->nameGeneration->getCustomerName($customer);
        $policyText = $this->policyRepository->getCurrentPolicy($storeId)->getContent();

        $template = $this->config->getValue('consent_notification/template', $storeId);
        $sender = $this->config->getValue('consent_notification/sender', $storeId);
        $replyTo = $this->config->getValue('consent_notification/reply_to', $storeId);
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        try {
            $status = ConsentQueue::STATUS_FAIL;
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )->setTemplateVars(
                [
                    'accountUrl' => $this->getAccountUrl($customer->getId(), $storeId),
                    'policyText' => $policyText
                ]
            )->setFrom(
                $sender
            )->addTo(
                $customer->getEmail(),
                $customerName
            )->setReplyTo(
                $replyTo
            )->getTransport();

            $transport->sendMessage();

            $status = ConsentQueue::STATUS_SUCCESS;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        try {
            $consentEntity->setStatus($status);
            $this->consentQueueRepository->save($consentEntity);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    public function getAccountUrl($customerId, $storeId)
    {
        return $this->storeManager->getStore($storeId)->getUrl(
            'gdpr/customer/login',
            [
                'customer_id' => $customerId,
                'key' => $this->generateKey($customerId)
            ]
        );
    }

    public function generateKey($customerId)
    {
        $salt = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);

        return $this->encryptor->getHash($customerId, $salt);
    }
}
