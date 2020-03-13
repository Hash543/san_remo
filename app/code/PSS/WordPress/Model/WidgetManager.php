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
use Magento\Framework\View\Layout;

/* Misc */
use PSS\WordPress\Block\Sidebar\Widget\AbstractWidget;

class WidgetManager
{	
	/*
	 * @var array
	 */
	protected $widgets = [];

	/*
	 * @var Layout
	 */
	protected $layout;
	
	/*
	 *
	 * @param  ModuleManaher $moduleManaher
	 * @return void
	 */
	public function __construct(array $widgets, Layout $layout)
	{
		$this->widgets = $widgets;
		$this->layout  = $layout;
	}
	
	/*
	 *
	 * @param  string @widgetName
	 * @return string|false
	 */
	public function getWidget($widgetName)
	{
		$widgetIndex = preg_match("/([0-9]{1,})$/", $widgetName, $widgetIndexMatch) ? (int)$widgetIndexMatch[1] : 0;
		$widgetName  = rtrim(preg_replace("/-[0-9]+$/i", '', $widgetName), '-');

		if (!isset($this->widgets[$widgetName])) {
			if (!isset($this->widgets['psw'])) {
				return false;
			}

			$this->widgets[$widgetName] = $this->widgets['psw'];
		}


		$widgetBlock = $this->layout->createBlock($this->widgets[$widgetName])
			->setWidgetType($widgetName)
			->setWidgetName($widgetName)
			->setWidgetIndex($widgetIndex);
		
		if ($widgetBlock instanceof AbstractWidget) {
			return $widgetBlock;
		}

		return false;
	}
}
