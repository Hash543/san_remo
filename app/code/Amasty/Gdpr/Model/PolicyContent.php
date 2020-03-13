<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Framework\Model\AbstractModel;

class PolicyContent extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\PolicyContent::class);
    }
}
