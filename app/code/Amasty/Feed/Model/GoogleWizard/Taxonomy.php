<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\ResourceModel\GoogleWizard\Taxonomy as ResourceTaxonomy;

class Taxonomy extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceTaxonomy::class);
    }
}
