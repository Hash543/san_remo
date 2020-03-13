<?php
// @codingStandardsIgnoreFile
namespace Alfa9\StoreInfo\Model\ResourceModel\Stores\Grid;

use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterface;

/**
 * Stockist collection backed by services
 */
class ServiceCollection extends AbstractServiceCollection
{
    /**
     * @var StockistRepositoryInterface
     */
    public $stockistRepository;

    /**
     * @var SimpleDataObjectConverter
     */
    public $simpleDataObjectConverter;

    /**
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param StockistRepositoryInterface $stockistRepository
     * @param SimpleDataObjectConverter $simpleDataObjectConverter
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StockistRepositoryInterface $stockistRepository,
        SimpleDataObjectConverter $simpleDataObjectConverter
    ) {
        $this->stockistRepository          = $stockistRepository;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
    }

    /**
     * Load customer group collection data from service
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->stockistRepository->getList($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            /** @var StockistInterface[] $stockists */
            $stockists = $searchResults->getItems();
            foreach ($stockists as $stockist) {
                $stockistItem = new DataObject();
                $stockistItem->addData(
                    $this->simpleDataObjectConverter->toFlatArray($stockist, StockistInterface::class)
                );
                $this->_addItem($stockistItem);
            }
            $this->_setIsLoaded();
        }
        return $this;
    }
}
