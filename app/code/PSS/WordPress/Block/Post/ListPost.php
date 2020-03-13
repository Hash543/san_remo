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
namespace PSS\WordPress\Block\Post;

use \PSS\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use \PSS\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class ListPost extends \PSS\WordPress\Block\Post
{
	/**
	 * Cache for post collection
	 *
	 * @var PostCollection
	 */
	protected $_postCollection = null;

    /**
     * Returns the collection of posts
     * @return bool|mixed|PostCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getPosts()
	{
		if ($this->_postCollection === null) {
			if ($this->getWrapperBlock()) {
				if ($this->_postCollection = $this->getWrapperBlock()->getPostCollection()) {
					if ($this->getPostType()) {
						$this->_postCollection->addPostTypeFilter($this->getPostType());
					}
				}
			}
			else {
				$this->_postCollection = $this->factory->create('PSS\WordPress\Model\ResourceModel\Post\Collection');
			}

			if ($this->_postCollection && ($pager = $this->getChildBlock('pager'))) {
				$pager->setPostListBlock($this)->setCollection($this->_postCollection);
			}
		}
		return $this->_postCollection;
	}

    /**
     * Sets the parent block of this block
     * This block can be used to auto generate the post list
     * @param AbstractWrapper $wrapper
     * @return ListPost
     */
	public function setWrapperBlock(AbstractWrapper $wrapper)
	{
		return $this->setData('wrapper_block', $wrapper);
	}
	
	/**
	 * Get the HTML for the pager block
	 *
	 * @return string
	 */
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}

    /**
     * @param \PSS\WordPress\Model\Post $post
     * @return \PSS\WordPress\Block\Post
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function renderPost(\PSS\WordPress\Model\Post $post)
	{
	    /** @var  \PSS\WordPress\Block\Post $postBlock */
		$postBlock = $this->getLayout()->createBlock('PSS\WordPress\Block\Post')->setPost($post);

		$templatesToTry = [
			'PSS_WordPress::post/list/renderer/' . $post->getPostType() . '.phtml',
			'PSS_WordPress::post/list/renderer/default.phtml'
		];
		
		foreach($templatesToTry as $templateToTry) {
			if ($this->getTemplateFile($templateToTry)) {
				$postBlock->setTemplate($templateToTry);
				break;
			}
		}
		return $postBlock->toHtml();
	}

    /**
     * @return \PSS\WordPress\Block\Post
     */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('PSS_WordPress::post/list.phtml');
		}
		return parent::_beforeToHtml();
	}
}
