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

/* Parent Class */
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

/* Constructor Args */
use Magento\Framework\App\Cache\Type\FrontendPool;

class Cache extends TagScope
{
	/*
	 * Cache type code unique among all cache types
	 *
	 * @const string
	 */
	const TYPE_IDENTIFIER = 'pss_wordPress';

	/*
	 * Cache tag used to distinguish the cache type from all other cache
	 *
	 * @const string
	*/
	const CACHE_TAG = 'PSS_WP';

	/*
	 *
	 *
	 *
	 */
	public function __construct(FrontendPool $cacheFrontendPool)
	{
		parent::__construct(
			$cacheFrontendPool->get(self::TYPE_IDENTIFIER), 
			self::CACHE_TAG
		);
	}
}
