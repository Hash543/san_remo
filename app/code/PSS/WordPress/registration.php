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
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'PSS_WordPress', __DIR__);

if (!function_exists('__')) {
	$bootstrap    = BP . '/app/bootstrap.php';
	$canIncludeFpFunctions = true;
	
	if (strpos(file_get_contents($bootstrap), 'app/functions.php') !== false) {
		$appFunctions = BP . '/app/functions.php';
		$fpFunctions  = __DIR__ . '/functions.php';
		
		if (is_file($appFunctions)) {
			$canIncludeFpFunctions = md5_file($appFunctions) === md5_file($fpFunctions);
		}
	}

	if ($canIncludeFpFunctions) {
		require __DIR__ . '/functions.php';	
	}
}
