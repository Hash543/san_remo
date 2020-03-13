<?php

namespace WeltPixel\NavigationLinks\Block\Html;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
	/**
	 * Recursively generates top menu html from data that is specified in $menuTree
	 *
	 * @param \Magento\Framework\Data\Tree\Node $menuTree
	 * @param string $childrenWrapClass
	 * @param int $limit
	 * @param array $colBrakes
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	protected function _getHtml(
			\Magento\Framework\Data\Tree\Node $menuTree,
			$childrenWrapClass,
			$limit,
			$colBrakes = []
	) {
		$html = '';
		
		$children = $menuTree->getChildren();
		$parentLevel = $menuTree->getLevel();
		$childLevel = $parentLevel === null ? 0 : $parentLevel + 1;
		
		$counter = 1;
		$itemPosition = 1;
		$childrenCount = $children->count();
		
		$parentPositionClass = $menuTree->getPositionClass();
		$itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
		
		/**
		 * Mega Menu: check if any child of the current parent has children
		 */
		$hasChildrenArr = [];
		foreach ($children as $child) {
			if ($childLevel == 1) {
				if ($child->hasChildren()) {
					$hasChildrenArr[$child->getParent()->getId()] = true;
					break;
				}
			}
		}
		/**
		 * End Mega Menu
		 */
		
		$liGroup = false;
		$remainingColumnsNumber = 0;
		foreach ($children as $child) {
			/**
			 * Mega Menu: settings
			 */
			$parent = $child->getParent();
			$hasChildren = '';
			
			$forceBreak = false;
			$width = '';
			
			if ($childLevel == 1) {
				
				if (isset($hasChildrenArr[$parent->getId()])) {
					$hasChildren = 'data-has-children="1"';
				}

				if ($parent->getWeltpixelMmDisplayMode() == 'boxed') {
                    $columnsNumber = '1';
                } else {
                    $columnsNumber = $parent->getWeltpixelMmColumnsNumber() ? trim($parent->getWeltpixelMmColumnsNumber()) : '4';
                }

				if ($columnsNumber) {
					// group items only if the number of subcetegories is bigger than columns numbers value
					if ($forceBreak || $childrenCount / $columnsNumber < 1) {
						$liGroup = 1;
					} else {
						$liGroup = (int) ceil($childrenCount / $columnsNumber);
					}
					
					if ($remainingColumnsNumber == 0) $remainingColumnsNumber = $columnsNumber;
				}
				
				// force break up ul if remaining children are not enough to fill all the remaining columns
				if (!$forceBreak) {
					$remainingChildren = ($childrenCount - $counter) + 1;
					
					if ($remainingChildren && $columnsNumber && $remainingChildren == $remainingColumnsNumber) {
						$liGroup = 1;
						$forceBreak = true;
					}
				}

				$numbers = preg_replace('/[^0-9]/', '', trim($parent->getWeltpixelMmColumnWidth()));
				$characters = preg_replace('/[^a-zA-Z%]/', '', trim($parent->getWeltpixelMmColumnWidth()));

				switch ($parent->getWeltpixelMmDisplayMode()) {
                    case 'fullwidth':
                        switch (trim(strtolower($characters))) {
                            case '%':
                                $columnWidth = (int)$numbers . '%';
                                break;
                            case 'px':
                                $columnWidth = (int)$numbers . 'px';
                                break;
                            default:
                                $columnWidth = (int)ceil(100 / $columnsNumber) . '%';
                        }
                        break;
                    case 'sectioned':
                        $columnWidth = 'auto';
                        break;
                    case 'boxed':
                        $columnWidth = false;
                        break;
                    default:
                        $columnWidth = false;
                        break;
                }
				
				$width = $columnWidth ? 'style="width: ' . $columnWidth . '"' : 'style="width: auto"';
				/**
				 * Mega Menu: open columns-group
				 */
				if ($parent->getWeltpixelMmDisplayMode() != 'default' && $counter == 1) {
					$html .= '<ul class="columns-group starter" ' . $width . '>';
				}
			}
			/**
			 * End Mega Menu
			 */
			
			$child->setLevel($childLevel);
			$child->setIsFirst($counter == 1);
			$child->setIsLast($counter == $childrenCount);
			$child->setPositionClass($itemPositionClassPrefix . $counter);
			
			$outermostClassCode = '';
			$outermostClass = $menuTree->getOutermostClass();
			
			$openInNewTab = '';
			if ($child->getData('open_in_newtab')) {
				$openInNewTab = ' target="_blank" ';
			}
			
			if ($childLevel == 0 && $outermostClass) {
				$outermostClassCode = ' class="' . $outermostClass . '" ';
				$child->setClass($outermostClass);
			}
			
			if ($colBrakes && count($colBrakes) && $colBrakes[$counter]['colbrake']) {
				$html .= '</ul></li><li class="column"><ul>';
			}
			
			$forceWidth = $childLevel == 1 && $parent->getWeltpixelMmDisplayMode() == 'sectioned' && $width != '' ? $width : '';
			
			$html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . ' ' . $hasChildren . ' ' . $forceWidth . '>';
			$html .= '<a' . $openInNewTab .' href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
							$child->getName()
					) . '</span></a>' . $this->_addSubMenu(
							$child,
							$childLevel,
							$childrenWrapClass,
							$limit
					) . '</li>';
			
			/**
			 * Mega Menu: close and re-open columns-group
			 */
			if ($parent->getWeltpixelMmDisplayMode() != 'default' && $liGroup && $childLevel == 1) {
				if ($liGroup == 1 && $counter != $childrenCount) {
					$html .= '</ul>';
					$html .= '<ul class="columns-group inner" ' . $width . '>';
					$remainingColumnsNumber--;
				} else {
					if (
							($counter % $liGroup == 0 && $counter > 1 && $counter != $childrenCount) ||
							($forceBreak && $counter != $childrenCount)
					) {
						$html .= '</ul>';
						$html .= '<ul class="columns-group inner" ' . $width . '>';
						$remainingColumnsNumber--;
					}
				}
			}
			
			/**
			 * Mega Menu: close columns-group
			 */
			if ($childLevel == 1 && $parent->getWeltpixelMmDisplayMode() != 'default' && $counter == $childrenCount) {
				$html .= '<span class="close columns-group last"></span>';
				$html .= '</ul>';
			}
			
			$itemPosition++;
			$counter++;
		}
		
		if ($colBrakes && count($colBrakes) && $limit) {
			$html = '<li class="column"><ul>' . $html . '</ul></li>';
		}
		
		return $html;
	}
	
	/**
	 * Add sub menu HTML code for current menu item
	 *
	 * @param \Magento\Framework\Data\Tree\Node $child
	 * @param string $childLevel
	 * @param string $childrenWrapClass
	 * @param int $limit
	 * @return string HTML code
	 */
	protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
	{
		$html = '';
		
		/**
		 * Mega Menu: settings
		 */
		$megaMenuClass = '';
		if ($childLevel == 0) {
			$megaMenuClass = $child->getWeltpixelMmDisplayMode() ? $child->getWeltpixelMmDisplayMode() : 'sectioned';
		}
		/**
		 * End Mega Menu
		 */
		
		if (!$child->hasChildren()) {
			return $html;
		}
		
		$colStops = null;
		if ($childLevel == 0 && $limit) {
			$colStops = $this->_columnBrake($child->getChildren(), $limit);
		}

		$html .= '<ul class="level' . $childLevel . ' submenu ' . $megaMenuClass . '" style="display: none;">';

        if ($childLevel == 0 && $megaMenuClass == 'fullwidth') {
            $html .= '<div class="fullwidth-wrapper">';
            $html .= '<div class="fullwidth-wrapper-inner">';
        }

		$html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);

        if ($childLevel == 0 && $megaMenuClass == 'fullwidth') {
            $html .= '</div>';
            $html .= '</div>';
        }

		$html .= '</ul><!-- end submenu -->';

		return $html;
	}
	
	/**
	 * Returns array of menu item's classes
	 *
	 * @param \Magento\Framework\Data\Tree\Node $item
	 * @return array
	 */
	protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
	{
		$classes = [];
		
		/**
		 * Mega Menu: settings
		 */
		$classes[] = 'megamenu';
		
		if ($item->getLevel() == 0) {
			$displayMode = $item->getWeltpixelMmDisplayMode() ? $item->getWeltpixelMmDisplayMode() : 'sectioned';
			$classes[] = $item->getClass() . '-' . $displayMode;
			$item->getUrl() == $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]) ?
				$classes[] = 'active' :
				$classes[] = '';
		}
		/**
		 * End Mega Menu
		 */
		
		$classes[] = 'level' . $item->getLevel();
		$classes[] = $item->getPositionClass();
		
		if ($item->getIsFirst()) {
			$classes[] = 'first';
		}
		
		if ($item->getIsActive()) {
			$classes[] = 'active';
		} elseif ($item->getHasActive()) {
			$classes[] = 'has-active';
		}
		
		if ($item->getIsLast()) {
			$classes[] = 'last';
		}
		
		if ($item->getClass()) {
			$classes[] = $item->getClass();
		}
		
		if ($item->hasChildren()) {
			$classes[] = 'parent';
		}
		
		return $classes;
	}
	
	protected function _toHtml()
	{
		$this->setModuleName($this->extractModuleName('Magento\Theme\Block\Html\Topmenu'));
		return parent::_toHtml();
	}
}