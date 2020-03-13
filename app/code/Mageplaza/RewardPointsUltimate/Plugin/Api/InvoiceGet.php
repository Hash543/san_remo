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
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceItemExtensionFactory;
use Mageplaza\RewardPointsUltimate\Model\InvoiceFactory;
use Mageplaza\RewardPointsUltimate\Model\InvoiceItemFactory;

/**
 * Class InvoiceGet
 * @package Mageplaza\RewardPointsUltimate\Plugin\Api
 */
class InvoiceGet
{
    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var InvoiceItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * @var InvoiceExtensionFactory
     */
    protected $invoiceExtensionFactory;

    /**
     * @var InvoiceItemExtensionFactory
     */
    protected $invoiceItemExtensionFactory;

    /**
     * InvoiceGet constructor.
     *
     * @param InvoiceItemFactory $invoiceItemFactory
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceExtensionFactory $invoiceExtensionFactory
     * @param InvoiceItemExtensionFactory $invoiceItemExtensionFactory
     */
    public function __construct(
        InvoiceItemFactory $invoiceItemFactory,
        InvoiceFactory $invoiceFactory,
        InvoiceExtensionFactory $invoiceExtensionFactory,
        InvoiceItemExtensionFactory $invoiceItemExtensionFactory
    ) {
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceItemFactory = $invoiceItemFactory;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->invoiceItemExtensionFactory = $invoiceItemExtensionFactory;
    }

    /**
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\InvoiceInterface $resultInvoice
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\InvoiceRepositoryInterface $subject,
        \Magento\Sales\Api\Data\InvoiceInterface $resultInvoice
    ) {
        $resultInvoice = $this->getInvoiceReward($resultInvoice);
        $resultInvoice = $this->getInvoiceItemReward($resultInvoice);

        return $resultInvoice;
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    protected function getInvoiceReward(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        $extensionAttributes = $invoice->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpReward()) {
            return $invoice;
        }

        try {
            /** @var \Mageplaza\RewardPointsUltimate\Api\Data\InvoiceInterface $rewardData */
            $rewardData = $this->invoiceFactory->create()->load($invoice->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $invoice;
        }

        /** @var \Magento\Sales\Api\Data\InvoiceExtension $invoiceExtension */
        $invoiceExtension = $extensionAttributes ? $extensionAttributes : $this->invoiceExtensionFactory->create();
        $invoiceExtension->setMpReward($rewardData);
        $invoice->setExtensionAttributes($invoiceExtension);

        return $invoice;
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    protected function getInvoiceItemReward(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        $invoiceItems = $invoice->getItems();
        if (null !== $invoiceItems) {
            /** @var \Magento\Sales\Api\Data\InvoiceItemInterface $invoiceItem */
            foreach ($invoiceItems as $invoiceItem) {
                $extensionAttributes = $invoiceItem->getExtensionAttributes();
                if ($extensionAttributes && $extensionAttributes->getMpReward()) {
                    continue;
                }

                try {
                    /** @var \Mageplaza\RewardPointsUltimate\Api\Data\InvoiceItemInterface $rewardData */
                    $rewardData = $this->invoiceItemFactory->create()->load($invoiceItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var \Magento\Sales\Api\Data\InvoiceItemExtension $invoiceItemExtension */
                $invoiceItemExtension = $extensionAttributes ? $extensionAttributes : $this->invoiceItemExtensionFactory->create();
                $invoiceItemExtension->setMpReward($rewardData);
                $invoiceItem->setExtensionAttributes($invoiceItemExtension);
            }
        }

        return $invoice;
    }

    /**
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $resultInvoice
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\InvoiceRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $resultInvoice
    ) {
        /** @var  $invoice */
        foreach ($resultInvoice->getItems() as $invoice) {
            $this->afterGet($subject, $invoice);
        }

        return $resultInvoice;
    }
}
