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

/* Interface */
use PSS\WordPress\Api\Data\Entity\ViewableInterface;

class Search extends AbstractResourcelessModel implements ViewableInterface
{
	/*
	 * @const string
	 */
	const ENTITY = 'wordpress_search';
	
	/*
	 * @const string
	 */
	const VAR_NAME = 's';

	/*
	 * @const string
	 */
	const VAR_NAME_POST_TYPE = 'post_type';
	
	/*
	 * Get the search term
	 *
	 * @return  string
	 */
  public function getSearchTerm()
  {
	  if (!$this->getData('search_term')) {
			return $this->wpContext->getRequest()->getParam(self::VAR_NAME);
		}

		return $this->getData('search_term');
  }

	/*
	 * Get the name of the search
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'Search results for ' . $this->getSearchTerm();
	}

	/*
	 * Get an array of post types
	 *
	 * @return array
	 */
	public function getPostTypes()
	{
		return $this->wpContext->getRequest()->getParam(self::VAR_NAME_POST_TYPE);	
	}
	
	/*
	 *
	 *
	 * @return  string
	 */
	public function getUrl()
	{
		$extra = '';
		
		if ($postTypes = $this->getPostTypes()) {
			foreach($postTypes as $postType) {
				$extra .= self::VAR_NAME_POST_TYPE . '[]=' . urlencode($postType) . '&';
			}
			
			$extra = '?' . rtrim($extra, '&');
		}
		
		return $this->url->getUrlWithFront('search/' . urlencode($this->getSearchTerm()) . '/' . $extra);
	}
}
