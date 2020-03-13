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

class Pages extends AbstractWidget
{
	/**
	 * Returns the currently loaded page model
	 *
	 * @return \PSS\WordPress\Model\Post
     * @throws \Exception
	 */
	public function getPost()
	{
		if (!$this->hasPost()) {
			$this->setPost(false);
			
			if ($post = $this->registry->registry('wordpress_post')) {
				if ($post->getPostType() === 'page') {
					$this->setPost($post);
				}
			}	
		}
		 
		 return $this->_getData('post');
	}
	
	/**
	 * Retrieve a collection  of pages
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	public function getPages()
	{
		return $this->getPosts();
	}

    /**
     * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getPosts()
	{
		$posts = $this->factory->create('Model\ResourceModel\Post\Collection')->addPostTypeFilter('page');

		if ($this->hasParentId()) {
			$posts->addPostParentIdFilter($this->getParentId());
		}
		else if ($this->getPost() && $this->getPost()->hasChildren()) {
			$posts->addPostParentIdFilter($this->getPost()->getId());
		}
		else {
			$posts->addPostParentIdFilter(0);
		}
		
		return $posts->addIsViewableFilter()->load();
	}
	
	/**
	 * Retrieve the block title
	 *
	 * @return string
     * @throws \Exception
	 */
	public function getTitle()
	{
		if ($this->getPost() && $this->getPost()->hasChildren()) {
			return $this->getPost()->getPostTitle();
		}
		
		return parent::getTitle();
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Pages');
	}

    /**
     * @return \PSS\WordPress\Block\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/pages.phtml');
		}
		
		return parent::_beforeToHtml();
	}
}
