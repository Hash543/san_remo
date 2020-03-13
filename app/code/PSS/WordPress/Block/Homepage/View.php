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
namespace PSS\WordPress\Block\Homepage;

class View extends \PSS\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getEntity()
	{
		if (!$this->hasEntity()) {
			if ($homepage = $this->registry->registry('wordpress_homepage')) {
				$this->setData('entity', $homepage->getBlogPage() ? $homepage->getBlogPage() : $homepage);
			}
			else {
				$this->setData('entity', false);
			}
		}
		
		return $this->getData('entity');
	}

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getIntroText()
	{
		return $this->getEntity() ? trim($this->getEntity()->getBlogDescription()) : '';
	}

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getBlogHomepageUrl()
	{
		return $this->getEntity()->getUrl();
	}

    /**
     * Determine whether the first page of posts are being displayed
     * @return bool
     */
	public function isFirstPage()
	{
		return $this->getRequest()->getParam('page', '1') === '1';
	}

    /**
     * Generates and returns the collection of posts
     * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
     */
	protected function _getPostCollection()
	{
		return parent::_getPostCollection()->addStickyPostsToCollection()->addPostTypeFilter('post');
	}
}
