<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoItemExtensionFactory;
use Mageplaza\RewardPointsUltimate\Model\CreditmemoFactory;
use Mageplaza\RewardPointsUltimate\Model\CreditmemoItemFactory;

/**
 * Class CreditmemoGet
 * @package Mageplaza\RewardPointsUltimate\Plugin\Api
 */
class CreditmemoGet
{
    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoItemFactory
     */
    protected $creditmemoItemFactory;

    /**
     * @var CreditmemoExtensionFactory
     */
    protected $creditmemoExtensionFactory;

    /**
     * @var CreditmemoItemExtensionFactory
     */
    protected $creditmemoItemExtensionFactory;

    /**
     * CreditmemoGet constructor.
     *
     * @param CreditmemoExtensionFactory $creditmemoExtensionFactory
     * @param CreditmemoItemExtensionFactory $creditmemoItemExtensionFactory
     */
    public function __construct(
        CreditmemoItemFactory $creditmemoItemFactory,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoExtensionFactory $creditmemoExtensionFactory,
        CreditmemoItemExtensionFactory $creditmemoItemExtensionFactory
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoItemFactory = $creditmemoItemFactory;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->creditmemoItemExtensionFactory = $creditmemoItemExtensionFactory;
    }

    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $resultCreditmemo
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $subject,
        \Magento\Sales\Api\Data\CreditmemoInterface $resultCreditmemo
    ) {
        $resultCreditmemo = $this->getCreditmemoReward($resultCreditmemo);
        $resultCreditmemo = $this->getCreditmemoItemReward($resultCreditmemo);

        return $resultCreditmemo;
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    protected function getCreditmemoReward(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo)
    {
        $extensionAttributes = $creditmemo->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpReward()) {
            return $creditmemo;
        }

        try {
            /** @var \Mageplaza\RewardPointsUltimate\Api\Data\CreditmemoInterface $rewardData */
            $rewardData = $this->creditmemoFactory->create()->load($creditmemo->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $creditmemo;
        }

        /** @var \Magento\Sales\Api\Data\CreditmemoExtension $creditmemoExtension */
        $creditmemoExtension = $extensionAttributes ? $extensionAttributes : $this->creditmemoExtensionFactory->create();
        $creditmemoExtension->setMpReward($rewardData);
        $creditmemo->setExtensionAttributes($creditmemoExtension);

        return $creditmemo;
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    protected function getCreditmemoItemReward(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo)
    {
        $creditmemoItems = $creditmemo->getItems();
        if (null !== $creditmemoItems) {
            /** @var \Magento\Sales\Api\Data\CreditmemoItemInterface $creditmemoItem */
            foreach ($creditmemoItems as $creditmemoItem) {
                $extensionAttributes = $creditmemoItem->getExtensionAttributes();

                if ($extensionAttributes && $extensionAttributes->getMpReward()) {
                    continue;
                }

                try {
                    /** @var \Mageplaza\RewardPointsUltimate\Api\Data\CreditmemoItemInterface $rewardData */
                    $rewardData = $this->creditmemoItemFactory->create()->load($creditmemoItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var \Magento\Sales\Api\Data\CreditmemoItemExtension $creditmemoItemExtension */
                $invoiceItemExtension = $extensionAttributes ? $extensionAttributes : $this->creditmemoItemExtensionFactory->create();
                $invoiceItemExtension->setMpReward($rewardData);
                $creditmemoItem->setExtensionAttributes($invoiceItemExtension);
            }
        }

        return $creditmemo;
    }

    /**
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $resultCreditmemo
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $resultCreditmemo
    ) {
        /** @var  $creditmemo */
        foreach ($resultCreditmemo->getItems() as $creditmemo) {
            $this->afterGet($subject, $creditmemo);
        }

        return $resultCreditmemo;
    }
}
