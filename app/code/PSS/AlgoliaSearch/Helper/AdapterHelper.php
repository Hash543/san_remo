<?php
/**
 * @author Israel Yasis
 */
namespace PSS\AlgoliaSearch\Helper;

/**
 * Class AdapterHelper
 * @package PSS\AlgolicaSearch\Helper
 */
class AdapterHelper extends \Algolia\AlgoliaSearch\Helper\AdapterHelper {
    /**
     * @var \Algolia\AlgoliaSearch\Helper\Adapter\FiltersHelper
     */
    private $filtersHelper;
    /**
     * @var \Algolia\AlgoliaSearch\Helper\ConfigHelper
     */
    private $configHelper;
    /**
     * AdapterHelper constructor.
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Algolia\AlgoliaSearch\Helper\Data $algoliaHelper
     * @param \Algolia\AlgoliaSearch\Helper\Adapter\FiltersHelper $filtersHelper
     * @param \Algolia\AlgoliaSearch\Helper\ConfigHelper $configHelper
     */
    public function __construct(
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        \Algolia\AlgoliaSearch\Helper\Data $algoliaHelper,
        \Algolia\AlgoliaSearch\Helper\Adapter\FiltersHelper $filtersHelper,
        \Algolia\AlgoliaSearch\Helper\ConfigHelper $configHelper
    ) {
        $this->filtersHelper = $filtersHelper;
        $this->configHelper = $configHelper;
        parent::__construct($catalogSearchHelper, $algoliaHelper, $filtersHelper, $configHelper);
    }

    /**
     * @override
     */
    public function isSearch()
    {
        return $this->filtersHelper->getRequest()->getFullActionName() === 'catalogsearch_result_index'
            &&  $this->configHelper->isInstantEnabled($this->getStoreId()) === true;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->configHelper->getStoreId();
    }
}