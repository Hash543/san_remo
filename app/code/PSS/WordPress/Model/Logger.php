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

class Logger extends \Monolog\Logger
{
  /*
   * Extended to add in calling object data to context array
   */
	public function addRecord($level, $message, array $context = array())
	{
		if ($backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)) {
			$context['backtrace'] = array_pop($backtrace);
		}

		parent::addRecord($level, $message, $context);
	}
}
