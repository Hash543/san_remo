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
namespace PSS\WordPress\Plugin\Magento\Framework\App\Router;

use \Magento\Framework\App\Router\ActionList;

class ActionListPlugin
{
	/**
	 * Magento 2 doesn't allow underscore in the module name
	 * So this fixes that and allows module names like PSS_WordPress_PostTypeTaxonomy to setup Controllers
	 * In the above example, WordPress is the vendor name and WordPress_PostTypeTaxonomy is the module name
	 *
	 * @param ActionList $subject
	 * @param Closure $callback
	 * @param string $module
	 * @param string $area
	 * @param string $namespace
	 * @param string $action
	 * @return string|null
	**/
	public function aroundGet(ActionList $subject, $callback, $module, $area, $namespace, $action)
	{
		if (strpos($module, 'PSS_WordPress_') !== 0) {
			return $callback($module, $area, $namespace, $action);
		}

		return str_replace('PSS', 'WordPress\\', $module)
			 . '\\Controller' 
			 . ($area ? '\\' . $area : $area)
			 . '\\' . ucwords($namespace)
			 . '\\' . ucwords($action);
	}
}
