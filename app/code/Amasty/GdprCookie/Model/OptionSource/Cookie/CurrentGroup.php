<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\OptionSource\Cookie;

use Magento\Framework\Option\ArrayInterface;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection;

class CurrentGroup implements ArrayInterface
{
    /**
     * @var Collection
     */
    private $linkCollection;

    public function __construct(
        Collection $linkCollection
    ) {
        $this->linkCollection = $linkCollection;
    }

    public function toOptionArray()
    {
        return [];
    }
}
