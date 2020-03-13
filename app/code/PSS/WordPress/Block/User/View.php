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
namespace PSS\WordPress\Block\User;

use PSS\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use PSS\WordPress\Model\User;

class View extends AbstractWrapper
{
	/**
	 * Caches and returns the current category
	 *
	 * @return \PSS\WordPress\Model\User
	 */
	public function getEntity()
	{
		return $this->registry->registry(User::ENTITY);
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected function _getPostCollection()
	{
		return parent::_getPostCollection()
			->addFieldToFilter('post_author', $this->getEntity() ? $this->getEntity()->getId() : 0);
	}
}
