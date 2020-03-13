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
namespace PSS\WordPress\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Autop extends AbstractHelper
{
    /**
     *  Call autop on the string
     * Then go through each shortcode and try to apply
     * Finally go through each shortcode again to check if another shortcode
     * has handled it and if so add it to the assetInjectionShortcodes array
     * @param $string
     * @return string
     */
	public function autop($string)
	{
		return $this->addParagraphTagsToString($string);
	}

    /**
     * @param $string
     * @return string
     */
	public function addParagraphTagsToString($string)
	{
		if ($this->_getFunctionFromWordPress('wpautop', 'wp-includes' . DIRECTORY_SEPARATOR . 'formatting.php', array(
			'wpautop',
			'wp_replace_in_html_tags',
			'_autop_newline_preservation_helper',
			'wp_html_split',
			'get_html_split_regex',
			'shortcode_unautop',
		))) {
			$string = fp_wpautop($string);

			$string = fp_shortcode_unautop($string);
			// Fix shortcodes that get P'd off!
			$string = preg_replace('/<p>\[/', '[', $string);
			$string = preg_replace('/\]<\/p>/', ']', $string);
		}

		return trim($string);
	}

    /**
     * @param $function
     * @param $file
     * @param array $depends
     * @return bool
     */
	protected function _getFunctionFromWordPress($function, $file, $depends = array())
	{
		$newFunction = 'fp_' . $function;
		
		if (function_exists($newFunction)) {
			return true;
		}

		// Get file from Magento
		$targetFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'WordPress' . DIRECTORY_SEPARATOR . $file;
		
		if (!is_file($targetFile)) {
			return false;
		}

		$code    = preg_replace('/\/\*\*.*\*\//Us', '', file_get_contents($targetFile));
		$depends = array_flip($depends);
		
		foreach($depends as $key => $value) {
			$depends[$key] = '';
		}

		foreach($depends as $function => $ignore) {
			if (preg_match('/(function ' . $function . '\(.*)function/sU', $code, $matches)) {
				$depends[$function] = $matches[1];
			}
			else {
				return false;
			}
		}

		$code = preg_replace('/(' . implode('|', array_keys($depends)) . ')/', 'fp_$1', implode("\n\n", $depends));
		
		@eval($code);

		return function_exists($newFunction);
	}
}
