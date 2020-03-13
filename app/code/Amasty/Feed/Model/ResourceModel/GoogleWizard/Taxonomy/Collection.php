<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\GoogleWizard\Taxonomy;

use Amasty\Feed\Model\ResourceModel\GoogleWizard\Taxonomy as ResourceTaxonomy;
use Amasty\Feed\Model\GoogleWizard\Taxonomy;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Taxonomy::class, ResourceTaxonomy::class);
    }
}
