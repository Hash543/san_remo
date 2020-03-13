<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Indexer;

class PageProduct extends \Amasty\Xlanding\Model\Indexer\AbstractIndexer
{
    const INDEXER_ID = 'amasty_xlanding_page_product';

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteRow($id)
    {
        $this->getIndexBuilder()->reindexByPageIds([$id]);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doExecuteList($ids)
    {
        $this->getIndexBuilder()->reindexByPageIds($ids);
        $this->getCacheContext()->registerTags($this->getIdentities());
    }
}
