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

class Network
{
	/*
	 * Determine whether the Network is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return false;
	}

	/*
	 * Get the site ID
	 *
	 * @return int
	 */
	public function getSiteId()
	{
		return 1;
	}

	/*
	 * Get the blog ID
	 *
	 * @return int
	 */
	public function getBlogId()
	{
		return 1;
	}

	/*
	 *
	 *
	 * return false
	 */
	public function getSiteAndBlogObjects()
	{
		return false;
	}
	
	/*
	 *
	 *
	 *
	 * @return false
	 */	
	public function getBlogTableValue($key)
	{		
		return false;
	}

	/*
	 *
	 *
	 *
	 * @return false
	 */
	public function getSitePath()
	{
		return false;
	}

	/*
	 *
	 *
	 *
	 * @return false
	 */
	public function getNetworkTables()
	{
		return false;
	}
}
