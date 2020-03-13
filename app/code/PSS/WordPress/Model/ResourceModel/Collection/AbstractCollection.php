<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractDbCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use PSS\WordPress\Model\Context as WPContext;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

abstract class AbstractCollection extends AbstractDbCollection
{
	/**
	 * @var WPContext
	 */
	protected $wpContext;

	/**
	 * @var OptionManager
	 */
	protected $optionManager;
	
	/**
	 * @var
	 */
	protected $postTypeManager;

    /**
     * AbstractCollection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param WPContext $wpContext
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
	public function __construct(
	    EntityFactoryInterface $entityFactory,
		LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
		WPContext $wpContext,
        AdapterInterface $connection  = null,
        AbstractDb $resource    = null
	) {
		$this->wpContext       = $wpContext;
		$this->optionManager   = $wpContext->getOptionManager();
		$this->postTypeManager = $wpContext->getPostTypeManager();
		
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
	}


	public function getConnection()
	{
		return $this->getResource()->getConnection();
	}
	
	/**
	 * Removes all order data set at the collection level
	 * This does not remove order set using self::getSelect()->order($field, $dir)
	 *
	 * @return $this
	 */
	public function resetOrderBy()
	{
		$this->_orders = array();
		
		return $this;
	}

	/**
	 * Force the collection to be empty
	 *
	 */
	public function forceEmpty()
	{
		$this->getSelect()->where('1=2')->limit(1);

		return $this;
	}
}
