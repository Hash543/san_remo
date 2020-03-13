<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface RuleInterface extends ExtensibleDataInterface
{
    /**
     * @return string|null
     */
    public function getPromoCats();

    /**
     * @param string $promoCats
     * @return $this
     */
    public function setPromoCats($promoCats);

    /**
     * @return string|null
     */
    public function getPromoSkus();

    /**
     * @param string $promoSkus
     * @return $this
     */
    public function setPromoSkus($promoSkus);

    /**
     * @return string|null
     */
    public function getApplyDiscountTo();

    /**
     * @param string $applyDiscountTo
     * @return $this
     */
    public function setApplyDiscountTo($applyDiscountTo);

    /**
     * @return string|null
     */
    public function getEachm();

    /**
     * @param string $eachm
     * @return $this
     */
    public function setEachm($eachm);

    /**
     * @return int|null
     */
    public function getPriceselector();

    /**
     * @param int $priceselector
     * @return $this
     */
    public function setPriceselector($priceselector);

    /**
     * @return string|null
     */
    public function getNqty();

    /**
     * @param string $nqty
     * @return $this
     */
    public function setNqty($nqty);

    /**
     * @return string|null
     */
    public function getMaxDiscount();

    /**
     * @param string $maxDiscount
     * @return $this
     */
    public function setMaxDiscount($maxDiscount);

    /**
     * @return int|null
     */
    public function getSkipRule();

    /**
     * @param int $skipRule
     * @return $this
     */
    public function setSkipRule($skipRule);
}
