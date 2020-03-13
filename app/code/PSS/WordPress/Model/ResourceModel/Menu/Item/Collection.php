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
namespace PSS\WordPress\Model\ResourceModel\Menu\Item;

use \PSS\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class Collection extends PostCollection
{
	/**
	 * Name prefix of events that are dispatched by model
	 *
	 * @var string
	*/
	protected $_eventPrefix = 'wordpress_menu_item_collection';
	
	/**
	 * Name of event parameter
	 *
	 * @var string
	*/
	protected $_eventObject = 'menu_items';
	
	/**
	 * Initialise the object
	 *
	 */
	public function _construct()
	{
    $this->_init('PSS\WordPress\Model\Menu\Item', 'PSS\WordPress\Model\ResourceModel\Menu\Item');
		
		$this->addPostTypeFilter('nav_menu_item');
	}
	
	/**
	 * Ensures that only posts and not pages are returned
	 * WP stores posts and pages in the same DB table
	 *
	 */
  protected function _initSelect()
  {
  	parent::_initSelect();

		$this->getSelect()->order('menu_order ASC');

		return $this;
	}
	
	/**
	 * Filter the collection by parent ID
	 * Set 0 as $parentId to return root menu items
	 *
	 * @param int $parentId = 0
	 * @return $this
	 */
	public function addParentItemIdFilter($parentId = 0)
	{
		return $this->addMetaFieldToFilter('_menu_item_menu_item_parent', $parentId);
	}
}
