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
namespace PSS\WordPress\Block\Sidebar\Widget;

class Categories extends AbstractWidget
{
	/**
	 * Returns the current category collection
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Term\Collection
	 */
	public function getCategories()
	{
		$collection = $this->factory->create('PSS\WordPress\Model\ResourceModel\Term\Collection')
				->addTaxonomyFilter($this->getTaxonomy())
				->addParentIdFilter($this->getParentId())
				->addHasObjectsFilter();
		$collection->getSelect()
			->reset('order')
			->order('name ASC');

		return $collection;
	}

    /**
     * @return string
     */
	public function getTaxonomy()
	{
		return $this->_getData('taxonomy') ? $this->_getData('taxonomy') : 'category';
	}
	
	/**
	 * Returns the parent ID used to display categories
	 * If parent_id is not set, 0 will be returned and root categories displayed
	 *
	 * @return int
	 */
	public function getParentId()
	{
		return number_format($this->getData('parent_id'), 0, '', '');
	}
	
	/**
	 * Determine whether the category is the current category
	 *
	 * @param \PSS\WordPress\Model\Category $category
	 * @return bool
     * @throws \Exception
	 */
	public function isCurrentCategory($category)
	{
		if ($this->getCurrentCategory()) {
			return $category->getId() == $this->getCurrentCategory()->getId();
		}
		
		return false;
	}
	
	/**
	 * Retrieve the current category
	 *
	 * @return \PSS\WordPress\Model\Category
     * @throws \Exception
	 */
	public function getCurrentCategory()
	{
		if (!$this->hasCurrentCategory()) {
			$this->setCurrentCategory($this->registry->registry('wordpress_term'));
		}
		
		return $this->getData('current_category');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Categories');
	}
	
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/categories.phtml');
		}

		return parent::_beforeToHtml();
	}
}
