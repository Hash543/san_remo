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
namespace PSS\WordPress\Block\Term;

use \PSS\WordPress\Model\Term;

class View extends \PSS\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	public function getEntity()
	{
		return $this->getTerm();
	}

	/**
	 * Returns the current Wordpress category
	 * This is just a wrapper for getCurrentCategory()
	 *
	 * @return \PSS\WordPress\Model\Term
	 */
	public function getTerm()
	{
		if (!$this->hasTerm()) {
			$this->setTerm($this->registry->registry(Term::ENTITY));
		}
		
		return $this->_getData('term');
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected function _getPostCollection()
	{
		if ($this->getTerm()) {
			return $this->getTerm()->getPostCollection();
		}
		
		return false;
	}
}
