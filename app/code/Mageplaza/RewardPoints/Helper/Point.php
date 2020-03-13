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

namespace Mageplaza\RewardPoints\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\Media;
use Mageplaza\RewardPoints\Model\Source\DisplayPointLabel;
use Mageplaza\RewardPoints\Model\Source\RoundingMethod;

/**
 * Class Point
 * @package Mageplaza\RewardPoints\Helper
 */
class Point extends Data
{
    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * Point constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Mageplaza\Core\Helper\Media $mediaHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        AssetRepository $assetRepo,
        Media $mediaHelper
    ) {
        $this->assetRepo = $assetRepo;
        $this->mediaHelper = $mediaHelper;

        parent::__construct($context, $objectManager, $storeManager, $priceCurrency, $timeZone);
    }

    /**
     * @param $amount
     * @param null $storeId
     *
     * @return mixed|string
     */
    public function format($amount, $storeId = null)
    {
        $amount = $this->round($amount, $storeId);
        if ($amount == 0) {
            return $this->getConfigGeneral('zero_amount', $storeId);
        }

        if (in_array($amount, [1, -1])) {
            $label = $this->getPointLabel($storeId);
        } else {
            $label = $this->getPluralPointLabel($storeId);
        }

        return ($this->getPointLabelPosition($storeId) == DisplayPointLabel::AFTER_AMOUNT)
            ? $amount . $label : $label . $amount;
    }

    /**
     * Format point
     *
     * @param $point
     * @param null $storeId
     *
     * @return float
     */
    public function round($point, $storeId = null)
    {
        $roundingMethod = $this->getConfigEarning('round_method', $storeId);
        switch ($roundingMethod) {
            case RoundingMethod::ROUNDING_DOWN:
                $point = floor($point);
                break;
            case RoundingMethod::ROUNDING_UP:
                $point = ceil($point);
                break;
            default:
                $point = round($point);
        }

        return $point;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getIconHtml($storeId = null)
    {
        if (!$this->getConfigGeneral('show_point_icon', $storeId)) {
            return '';
        }

        $icon = $this->getConfigGeneral('icon', $storeId);
        if ($icon && $this->mediaHelper->getMediaDirectory()->isExist('mageplaza/rewardpoints/' . $icon)) {
            $iconUrl = $this->mediaHelper->getMediaUrl('mageplaza/rewardpoints/' . $icon);
        } else {
            $iconUrl = $this->assetRepo->getUrlWithParams(
                'Mageplaza_RewardPoints::images/default/point.png',
                ['_secure' => $this->_getRequest()->isSecure()]
            );
        }

        return '<img src="' . $iconUrl . '" alt="' . __('Reward Points') . '" width="15" height="15" />';
    }

    /**
     * Get zero point label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getZeroPointLabel($storeId = null)
    {
        return $this->getConfigGeneral('zero_amount', $storeId);
    }

    /**
     * Get point Label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPointLabel($storeId = null)
    {
        return $this->getConfigGeneral('point_label', $storeId);
    }

    /**
     * Get plural point label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPluralPointLabel($storeId = null)
    {
        return $this->getConfigGeneral('plural_point_label', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPointLabelPosition($storeId = null)
    {
        return $this->getConfigGeneral('display_point_label', $storeId);
    }
}
