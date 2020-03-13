<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\DeleteRequest;

use Amasty\Gdpr\Model\Config;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\SenderResolverInterface;

class Notifier
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $nameGeneration;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        TransportBuilder $transportBuilder,
        CustomerNameGenerationInterface $nameGeneration,
        SenderResolverInterface $senderResolver
    ) {
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->nameGeneration = $nameGeneration;
        $this->senderResolver = $senderResolver;
    }

    /**
     * @param $customerId
     * @param $comment
     *
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function notify($customerId, $comment)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return;
        }

        $customerName = $this->nameGeneration->getCustomerName($customer);

        $template = $this->config->getValue('deletion_notification/deny_template');
        $sender = $this->config->getValue('deletion_notification/deny_sender');
        $replyTo = $this->config->getValue('deletion_notification/deny_reply_to');
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $transport = $this->transportBuilder
            ->setTemplateIdentifier(
                $template
            )
            ->setTemplateOptions(
                [
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $customer->getStoreId()
                ]
            )
            ->setTemplateVars(
                [
                    'customer' => $customer,
                    'customerName' => $customerName,
                    'comment' => $comment
                ]
            )
            ->setFrom(
                $sender
            )
            ->addTo(
                $customer->getEmail(),
                $customerName
            )->setReplyTo(
                $replyTo
            )->getTransport();

        $transport->sendMessage();
    }

    /**
     * @param string|int $customerId
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function notifyAdmin($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);

        $customerName = $this->nameGeneration->getCustomerName($customer);

        $template = $this->config->getAdminNotificationTemplate();
        $sender = $this->config->getAdminNotificationSender();
        $recievers = preg_split('/\n|\r\n?/', $this->config->getAdminNotificationReciever());

        foreach ($recievers as $reciever) {
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )
            ->setTemplateOptions(
                [
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $customer->getStoreId()
                ]
            )
            ->setTemplateVars(
                [
                    'customerName' => $customerName
                ]
            )
            ->setFrom(
                $sender
            )
            ->addTo(
                $reciever
            )
            ->getTransport();

            $transport->sendMessage();
        }
    }
}
