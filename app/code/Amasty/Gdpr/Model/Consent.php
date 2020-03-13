<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Gdpr\Model\Visitor;

class Consent
{
    const FROM_REGISTRATION = 'registration';

    const FROM_CHECKOUT = 'checkout';

    const FROM_SUBSCRIPTION = 'subscription';

    const FROM_CONTACTUS = 'contactus';

    const FROM_EMAIL = 'email';

    /**
     * @var WithConsentRepositoryInterface
     */
    private $withConsentRepository;

    /**
     * @var WithConsentFactory
     */
    private $consentFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var ActionLogger
     */
    private $logger;

    /**
     * @var WithConsentResource
     */
    private $withConsent;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Gdpr\Model\Visitor
     */
    private $visitor;

    public function __construct(
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory,
        PolicyRepositoryInterface $policyRepository,
        ActionLogger $logger,
        WithConsentResource $withConsent,
        StoreManagerInterface $storeManager,
        Visitor $visitor
    ) {
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->policyRepository = $policyRepository;
        $this->logger = $logger;
        $this->withConsent = $withConsent;
        $this->storeManager = $storeManager;
        $this->visitor = $visitor;
    }

    /**
     * @param string|int $customerId
     * @param string $from
     *
     * @return void
     */
    public function acceptLastVersion($customerId, $from)
    {
        if (!$customerId) {
            return;
        }

        if ($policy = $this->policyRepository->getCurrentPolicy()) {
            $consents = $this->withConsent->getConsentsByCustomerId($customerId);
            foreach ($consents as $consent) {
                if ($consent['policy_version'] === $policy->getPolicyVersion()) {
                    return;
                }
            }

            /** @var WithConsent $consent */
            $consent = $this->consentFactory->create();

            $consent->setPolicyVersion($policy->getPolicyVersion());
            $consent->setGotFrom($from);
            $consent->setWebsiteId($this->storeManager->getWebsite()->getId());
            $consent->setIp($this->visitor->getRemoteIp());
            $consent->setCustomerId($customerId);

            $this->withConsentRepository->save($consent);

            $this->logger->logAction('consent_given', $customerId);
        }
    }
}
