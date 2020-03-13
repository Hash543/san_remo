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

class Posts extends AbstractWidget
{
	/**
	 * Cache for post collection
	 *
	 * @var \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected $collection = null;
    
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		parent::_beforeToHtml();

		$this->setPosts($this->_getPostCollection());
		
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/posts.phtml');
		}
		return $this;
	}
	
	/**
	 * Control the number of posts displayed
	 *
	 * @param int $count
	 * @return $this
	 */
	public function setPostCount($count)
	{
		return $this->setNumber($count);
	}
	
	/**
	 * Retrieve the number of posts to display
	 * If the pager is enabled, this is posts per page
	 *
	 * @return int
	 */
	public function getNumber()
	{
		return $this->_getData('number') ? $this->_getData('number') : 5;
	}
	
	/**
	 * Adds on cateogry/author ID filters
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected function _getPostCollection()
	{
		if (is_null($this->collection)) {
			$collection = $this->factory->create('Model\ResourceModel\Post\Collection')
					->setOrderByPostDate()
					->addIsViewableFilter()
					->setPageSize($this->getNumber())
					->setCurPage(1);
	
			if ($categoryId = $this->getCategoryId()) {
				if (strpos($categoryId, ',') !== false) {
					$categoryId = explode(',', trim($categoryId, ','));
				}

				$collection->addCategoryIdFilter($categoryId);
			}
			
			if ($authorId = $this->getAuthorId()) {
				$collection->addFieldToFilter('post_author', $authorId);
			}
			
			if ($tag = $this->getTag()) {
				$collection->addTermFilter($tag, 'post_tag', 'name');
			}

			if ($postTypes = $this->getPostType()) {
				$collection->addPostTypeFilter(explode(',', $postTypes));
			}
			else {
				$collection->addPostTypeFilter('post');
			}

			$this->collection = $collection;
		}
		
		return $this->collection;
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		if ($this->getCategory()) {
			return $this->getCategory()->getName();
		}
		
		return __('Recent Posts');
	}
	
	/**
	 * Retrieve the category model used to filter the posts
	 *
	 * @return \PSS\WordPress\Model\Term|false
	 */
	public function getCategory()
	{
		if (!$this->hasCategory()) {
			$this->setCategory(false);
			if ($this->getCategoryId()) {
				$category = $this->factory->create('Term')->setTaxonomy('category')->load($this->getCategoryId());

				if ($category->getId()) {
					$this->setCategory($category)->setCategoryId($category->getId());
				}
			}
		}
		
		return $this->_getData('category');
	}
	
	/**
	 * Retrieve the category ID
	 *
	 * return int|null
	 */
	public function getCategoryId()
	{
		if ($categoryId = $this->_getData('category_id')) {
			return $categoryId;
		}
		
		return $this->_getData('cat');	
	}
	
	/**
	 * Retrieve the ID used for the list
	 * This is necessary so multiple instances can be used
	 *
	 * @return string
	 */
	public function getListId()
	{
		if (!$this->hasListId()) {
			$hash = 'wp-' . md5(rand(1111, 9999) . $this->getCategoryId() . $this->getAuthorId() . $this->getTitle());
			
			$this->setListId(substr($hash, 0, 6));
		}
		
		return $this->_getData('list_id');
	}
	
	/**
	 * Added to support 'Category Posts Widget' WP plugin
	 *
	 */
	public function canDisplayCommentCount()
	{
		return $this->_getData('comment_num') == 'on';
	}
	
	/**
	 * Determine whether we can display the date
	 *
	 * @return bool
	 */
	public function canDisplayDate()
	{
		return $this->_getData('date') == 'on';
	}
	
	/**
	 * Determine whether we can display the excerpt
	 *
	 * @return bool
	 */
	public function canDisplayExcerpt()
	{
		return $this->getData('excerpt') == 'on';
	}
	
	/**
	 * Determine whether we can display the image
	 *
	 * @return bool
	 */
	public function canDisplayImage()
	{
		return $this->getData('thumb') === 'on';
	}
	
	/**
	 * Determine whether we can display the title link
	 *
	 * @return bool
	 */
	public function canDisplayTitleLink()
	{
		return $this->getData('title_link') == 'on';
	}
	
	/**
	 * Retrieve the excerpt length
	 *
	 * @return null|int
	 */
	public function getExcerptLength()
	{
		if ($this->canDisplayExcerpt()) {
			return $this->_getData('excerpt_length');
		}
		
		return null;
	}
	
	/**
	 * Retrieve a string indicating the number of comments
	 *
	 * @param \PSS\WordPress\Model\Post $post
	 * @return string
	 */
	public function getCommentCountString(\PSS\WordPress\Model\Post $post)
	{
		if ($post->getCommentCount() == 0) {
			return __('No Comments');
		}
		else if ($post->getCommentCount() > 1) {
			return __('%1 Comments', $post->getCommentCount());
		}

		return __('1 Comment');	
	}
}
