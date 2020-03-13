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
namespace PSS\WordPress\Model;

use \PSS\WordPress\Model\Term;

class Menu extends Term
{
	/*
	 *
	 */
	const ENTITY = 'wordpress_menu';

	/*
	 * @const string
	 */
	const CACHE_TAG = 'wordpress_menu';
	
	/*
	 * Event data
	 *
	 * @var string
	 */
	protected $_eventPrefix      = 'wordpress_menu';
	protected $_eventObject      = 'menu';
	
	/*
	 * Cache to stop menu being generated multiple times
	 *
	 * @var array
	 */
	protected $_menuCache	 = null;
	
	/*
	 *
	 */
	public function _construct()
	{
        $this->_init('PSS\WordPress\Model\ResourceModel\Menu');
		
		return parent::_construct();
	}
	
	/*
	 * Gets a simple array of the menu
	 * For the actual menu item objects, use getMenuTreeObjects
	 *
	 * @return array|false
	 */
	public function getMenuTreeArray()
	{
		if ($tree = $this->getMenuTreeObjects()) {
			$menu = array();
			
			foreach($tree as $node) {
				$menu[] = $this->_getMenuTreeArray($node);
			}
			
			return $menu;
		}
		
		return false;
	}
	
	/*
	 *
	 */
	protected function _getMenuTreeArray($node)
	{
		$data = array(
			'id' => 'wp-' . $node->getId(),
			'label' => $node->getLabel(),
			'url' => $node->getUrl(),
			'css_class' => $node->getCssClass(),
			'title' => $node->getTitle(),
			'description' => $node->getDescription(),
			'target' => $node->getTarget(),
		);
		
		$children = $node->getChildrenItems();

		if (count($children) > 0) {
			$data['children'] = array();
			
			foreach($children as $child) {
				$data['children'][] = $this->_getMenuTreeArray($child);
			}
		}
		
		return $data;
	}
	
	/*
	 *
	 */
	public function getMenuTreeObjects()
	{
		if (null !== $this->_menuCache) {
			return $this->_menuCache;
		}
		
		$this->_menuCache = false;
		
		$items = $this->getMenuItems();
		
		if (count($items) > 0) {
			foreach($items as $item) {
				$this->_menuCache[] = $this->_getMenuTreeObjects($item);
			}
		}
		
		return $this->_menuCache;
	}
	
	/*
	 *
	 */
	protected function _getMenuTreeObjects($item)
	{
		$children = $item->getChildrenItems();

		if (count($children) > 0) {
			foreach($children as $child) {
				$this->_getMenuTreeObjects($child);
			}
		}
		
		return $item;
	}

	/*
	 * Retrieve the root menu items
	 *
	 * @return PSS_WordPress_Model_Resource_Menu_Item_Collection
	 */
	public function getMenuItems()
	{
		return $this->_getObjectResourceModel()
			->addIsViewableFilter()
			->addTermIdFilter($this->getId(), $this->getTaxonomy());
	}

	/*
	 * Retrieve the taxonomy type
	 *
	 * @return string
	 */
	public function getTaxonomy()
	{
		return 'nav_menu';
	}

	/*
	 * Retrieve the object resource model
	 *
	 * @return PSS_WordPress_Model_Resource_Post_Collection
	 */    
  protected function _getObjectResourceModel()
  {
    return $this->factory->create('PSS\WordPress\Model\ResourceModel\Menu\Item\Collection')->addParentItemIdFilter(0);
  }
}
