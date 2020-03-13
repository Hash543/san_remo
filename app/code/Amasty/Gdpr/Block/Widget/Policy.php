<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block\Widget;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Amasty\Gdpr\Model\Config;

class Policy extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'widget/policycontent.phtml';

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * Policy widget constructor.
     *
     * @param Template\Context          $context
     * @param PolicyRepositoryInterface $policyRepository
     * @param Config                    $configProvider
     * @param array                     $data
     */
    public function __construct(
        Template\Context $context,
        PolicyRepositoryInterface $policyRepository,
        Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->policyRepository = $policyRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getPolicyText()
    {
        if ($this->configProvider->isModuleEnabled()) {
            $policy = $this->policyRepository->getCurrentPolicy(
                $this->_storeManager->getStore()->getId()
            );

            if ($policy) {
                return $policy->getContent();
            }
        }

        return '';
    }
}
