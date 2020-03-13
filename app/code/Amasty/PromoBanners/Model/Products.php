<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBanners
 */


namespace Amasty\PromoBanners\Model;

use Magento\Framework\Model\AbstractModel;

class Products extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Amasty\PromoBanners\Model\ResourceModel\Products');
    }
}
