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
namespace PSS\WordPress\Api\Data\Entity;

/**
 * Interface for all entities in the integration that are viewable on the frontend
 * By viewable, it means that the entity has it's own page (eg. posts, categories, tags, users etc)
**/
interface ViewableInterface
{
	/**
	 * Get the item's name (for a post, this is the post title)
	 *
	 * @return  string
	**/
	public function getName();

	/**
	 *
	 *
	 * @return  string
	**/
	public function getUrl();
		
	/**
	 *
	 *
	 * @return  string
	**/
	public function getContent();
	
	/**
	 *
	 *
	 * @return \PSS\WordPress\Model\Image
	**/
	public function getImage();
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getPageTitle();

	/**
	 *
	 *
	 * @return  string
	**/	
	public function getMetaDescription();

	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaKeywords();
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getRobots();
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getCanonicalUrl();		
}
