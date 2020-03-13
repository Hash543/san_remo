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

/* Constructor Args */
use PSS\WordPress\Helper\Autop;
use Magento\Cms\Model\Template\FilterProvider;

class ShortcodeManager
{
	/*
	 * @var array
	 */
	protected $shortcodes = [];
	
	/*
	 * @var Autop
	 */
	protected $autop;

	/*
	 * @var FilterProvider
	 */
	protected $filterProvider;
	
	/*
	 *
	 *
	 *
	 */
	public function __construct(Autop $autop, FilterProvider $filterProvider, array $shortcodes = [])	
	{
		$this->autop          = $autop;
		$this->filterProvider = $filterProvider;
		
		foreach($shortcodes as $alias => $shortcode) {
			if (!method_exists($shortcode, 'isEnabled') || $shortcode->isEnabled()) {
				$this->shortcodes[$alias] = $shortcode;
			}
		}
	}

	/*
	 *
	 *
	 *
	 */
	public function renderShortcode($input, $args = [])
	{
		if ($args && is_object($args)) {
			$args = ['object' => $args];
		}

		// Apply Magento block/template filters
		$input = $this->filterProvider->getBlockFilter()->filter($input);

		if ($shortcodes = $this->getShortcodes()) {
			foreach($shortcodes as $shortcode) {
				// Legacy support. Old shortcodes returned false when not required
				if (($returnValue = $shortcode->renderShortcode($input, $args)) !== false) {
					$input = $returnValue;
				}
			}
		}

		return $input;
	}
	
	/*
	 *
	 *
	 *
	 */
	public function getShortcodes()
	{
		return $this->shortcodes;
	}

	/*
	 *
	 *
	 *
	 */
	public function getShortcodesThatRequireAssets()
	{
		$buffer = [];
		
		foreach($this->shortcodes as $alias => $shortcode) {
			if (method_exists($shortcode, 'requiresAssetInjection') && $shortcode->requiresAssetInjection()) {
				$buffer[$alias] = $shortcode;
			}
		}

		return $buffer;
	}
	
	/*
	 *
	 *
	 * @param  string $string
	 * @return string
	 */
	public function addParagraphTagsToString($string)
	{
		return $this->autop->addParagraphTagsToString($string);
	}
}
