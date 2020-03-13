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
namespace PSS\WordPress\Block\Post\PostList\Wrapper;

use PSS\WordPress\Block\AbstractBlock;

abstract class AbstractWrapper extends AbstractBlock
{
    /**
     * @return \Magento\Framework\Model\AbstractModel
     */
	abstract public function getEntity();

    /**
     * @return AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _prepareLayout()
	{
		if ($this->getEntity()) {
			$this->getEntity()->applyPageConfigData($this->pageConfig);
		}

		return parent::_prepareLayout();
	}

    /**
     * @return string
     * @throws \Exception
     */
	public function getIntroText()
	{
		return $this->getEntity() ? $this->getEntity()->getContent() : '';
	}
	
	/**
	 * Returns the collection of posts
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
     * @throws \Exception
	 */
	public function getPostCollection()
	{
		if (!$this->hasPostCollection()  && ($collection = $this->_getPostCollection()) !== false) {
			$collection->addIsViewableFilter()->addOrder('post_date', 'desc');
			
			$this->setPostCollection($collection);
			
			$collection->setFlag('after_load_event_name', $this->_getPostCollectionEventName() . '_after_load');
			$collection->setFlag('after_load_event_block', $this);
		}

		return $this->_getData('post_collection');
	}
	
	/**
	 * Retrieve the event name for before the post collection is loaded
	 *
	 * @return string
	 */
	protected function _getPostCollectionEventName()
	{
		$class = get_class($this);
		return 'wordpress_block_' . strtolower(substr($class, strpos($class, 'Block')+6)) . '_post_collection';
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected function _getPostCollection()
	{
		return $this->factory->create('Model\ResourceModel\Post\Collection');
	}

	/**
	 * Returns the HTML for the post collection
	 *
	 * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getPostListHtml()
	{
		if (($postListBlock = $this->getPostListBlock()) !== false) {
			return $postListBlock->toHtml();
		}
		
		return '';
	}

    /**
     * @return bool|\PSS\WordPress\Block\Post\ListPost
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getPostListBlock()
	{
		if (!($postListBlock = $this->getChildBlock('wp.post.list'))) {
			$postListBlock = $this->getLayout()
				->createBlock('PSS\WordPress\Block\Post\ListPost')
				->setTemplate('PSS_WordPress::post/list.phtml');
				
				$this->setChild('wp.post.list', $postListBlock);
		}

		if ($postListBlock && !$postListBlock->getWrapperBlock()) {
			$postListBlock->setWrapperBlock($this);
		}
		return $postListBlock;
	}

	/**
	 * Ensure a template is set
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		parent::_beforeToHtml();

		if (!$this->getTemplate()) {
			$this->setTemplate('Pss_WordPress::post/list/wrapper.phtml');
		}

		return $this;
	}
}
