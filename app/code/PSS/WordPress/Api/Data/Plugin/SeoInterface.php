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
namespace PSS\WordPress\Api\Data\Plugin;

interface SeoInterface
{
    /**
     * @param $post
     * @param $callback
     * @return string
     */
	public function aroundGetPageTitle($post, $callback);

    /**
     * @param $post
     * @param $callback
     * @return string
     */
	public function aroundGetMetaDescription($post, $callback);

    /**
     * @param $post
     * @param $callback
     * @return string
     */
	public function aroundGetMetaKeywords($post, $callback);

    /**
     * @param $post
     * @param $callback
     * @return string
     */
	public function aroundGetRobots($post, $callback);

    /**
     * @param $post
     * @param $callback
     * @return string
     */
	public function aroundGetCanonicalUrl($post, $callback);
}
