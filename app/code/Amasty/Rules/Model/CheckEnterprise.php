<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

class CheckEnterprise
{
    const B2B_NAME = 'B2B';

    const ENTERPRISE_NAME = 'Enterprise';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->metadata = $metadata;
        $this->logger = $logger;
    }

    /**
     * Check for enterprise or B2B edition
     *
     * @return bool
     */
    public function isEnterprise()
    {
        return $this->metadata->getEdition() === self::B2B_NAME
            || $this->metadata->getEdition() === self::ENTERPRISE_NAME;
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     * @return mixed
     */
    public function getRuleId(\Magento\Rule\Model\AbstractModel $rule)
    {
        try {
            return $this->isEnterprise() ? $rule->getRowId() : $rule->getId();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
