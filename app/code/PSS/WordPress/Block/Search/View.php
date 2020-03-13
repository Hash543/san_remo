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
namespace PSS\WordPress\Block\Search;

use PSS\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;

class View extends AbstractWrapper
{
    /**
     * @return \Magento\Framework\Model\AbstractModel
     */
	public function getEntity()
	{
		return $this->registry->registry('wordpress_search');
	}

    /**
     * Generates and returns the collection of posts
     * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
     */
	protected function _getPostCollection()
	{
		$collection = parent::_getPostCollection()	
			->addSearchStringFilter($this->_getParsedSearchString(), array('post_title' => 5, 'post_content' => 1));

		$searchablePostTypes = $this->getRequest()->getParam('post_type');
		
		if (!$searchablePostTypes) {
			$postTypes = $this->wpContext->getPostTypeManager()->getPostTypes();
			$searchablePostTypes = array();
			
			foreach($postTypes as $postType) {
				if ($postType->isSearchable()) {
					$searchablePostTypes[] = $postType->getPostType();
				}
			}
		}
		
		if (!$searchablePostTypes) {
			$searchablePostTypes = array('post', 'page');
		}
		
		return $collection->addPostTypeFilter($searchablePostTypes);
	}

    /**
     * Retrieve a parsed version of the search string
     * If search by single word, string will be split on each space
     * @return array
     */
	protected function _getParsedSearchString()
	{
		$words = explode(' ', $this->getSearchTerm());
        $maxWords = 15;
		if (count($words) > $maxWords) {
			$words = array_slice($words, 0, $maxWords);
		}
		foreach($words as $it => $word) {
			if (strlen($word) < 3) {
				unset($words[$it]);
			}
		}
		return $words;
	}

    /**
     * @param bool $escape
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getSearchTerm($escape = false)
	{
		return $this->getEntity()->getSearchTerm($escape);
	}

    /**
     * @return string
     */
	public function getSearchVar()
	{
		return $this->_getData('search_var') ? $this->_getData('search_var') : 's';
	}
}
