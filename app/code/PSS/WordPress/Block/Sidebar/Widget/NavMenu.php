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
namespace PSS\WordPress\Block\Sidebar\Widget;

class NavMenu extends AbstractWidget
{
	/**
	 * Get the menu model for the current widget
	 *
	 * @return \PSS\WordPress\Model\Menu
     * @throws \Exception
	 */
	public function getMenu()
	{
		if (!$this->hasMenu()) {
			$this->setMenu(false);

			$menu = $this->factory->create('Menu')->load($this->_getData('nav_menu'));

			if ($menu->getId()) {
				$this->setMenu($menu);
			}
		}

		return $this->_getData('menu');
	}

	/**
	 * Retrieve the default title
	 *
	 * @return string
     * @throws \Exception
	 */
	public function getDefaultTitle()
	{
		if ($this->getMenu()) {
			return $this->getMenu()->getName();
		}

		return __('Menu');
	}

	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/navmenu.phtml');
		}

		return parent::_beforeToHtml();
	}

	/**
	 * Recursively uild and return tree html
	 *
	 * @return string
     * @throws \Exception
	 */
	public function getTreeHtml()
	{
		if ($this->getMenu()) {
			return $this->_getTreeHtmlLevel(0, $this->getMenu()->getMenuTreeObjects());
		}
		
		return '';
	}

	/**
	 * Build and return a single level of tree html and recurse to render sub items
	 *
	 * @param int $level Menu level (0-index)
	 * @param \PSS\WordPress\Model\Menu\Item[] $menuTreeObjects Collection of menu items
	 * @return string
	 */
	protected function _getTreeHtmlLevel($level, $menuTreeObjects)
	{
		$indentString = str_repeat("\t", $level);

		$html = '';

		foreach ($menuTreeObjects as $current) {
			$classes = [
				'menu-item',
				'menu-item-' . $current->getId(),
				'menu-item-type-' . $current->getItemType(),
				'menu-item-object-' . $current->getObjectType(),
			];

			if (count($current->getChildrenItems())) {
				$classes[] = 'menu-item-has-children';
			}

			$html .= $indentString . '<li id="menu-item-' . $current->getId() . '" class="' . implode(' ', $classes) . '">' . PHP_EOL;
			$html .= $indentString . "\t" . '<a href="' . $this->escapeHtml($current->getUrl()) . '" title="' . $this->escapeHtml($current->getLabel()) . '">';
			$html .= $this->escapeHtml($current->getLabel()) . '</a>' . PHP_EOL;

			if (count($current->getChildrenItems())) {
				$html .= $indentString . "\t" . '<ul class="sub-menu">' . PHP_EOL;
				$html .= $this->_getTreeHtmlLevel($level + 1, $current->getChildrenItems()->getItems());
				$html .= $indentString . "\t" . '</ul>' . PHP_EOL;
			}

			$html .= $indentString . '</li>' . PHP_EOL;
		}

		return $html;
	}
}
