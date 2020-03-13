<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class PdfReward
 * @package Mageplaza\RewardPoints\Model\Sales\Pdf
 */
abstract class PdfReward extends DefaultTotal
{
    /**
     * @var \Mageplaza\RewardPoints\Helper\Data
     */
    protected $helperData;

    /**
     * @var $isUsePointLabel
     */
    protected $isUsePointLabel = true;

    /**
     * RewardDiscount constructor.
     *
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Mageplaza\RewardPoints\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        if (intval($this->getAmount()) != 0) {
            $totals = [
                [
                    'amount'     => $this->getAmountFormat(),
                    'label'      => __($this->getLabel()),
                    'font_size'  => $fontSize,
                    'sort_order' => $this->getSortOrder()
                ],
            ];

            return $totals;
        }
    }

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return;
    }

    /**
     * @return mixed|string
     */
    public function getAmountFormat()
    {
        $storeId = $this->getOrder()->getStoreId();

        return $this->isUsePointLabel
            ? $this->helperData->getPointHelper()->format(round($this->getAmount()), $storeId)
            : $this->getOrder()->formatPriceTxt($this->getAmount());
    }
}
