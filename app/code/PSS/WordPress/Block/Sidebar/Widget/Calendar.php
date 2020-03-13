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

use Magento\Setup\Exception;

class Calendar extends AbstractWidget
{
	/**
	 * Retrieve the default title
	 *
	 * @return null
	 */
	public function getDefaultTitle()
	{
		return $this->_getData('default_title');
	}

	/**
	 * Retrieve an array of date items separated into rows
	 *
	 * @return array
     * @throws \Exception
	 */
	public function getDateItems()
	{
		if (!$this->hasDateItems()) {
			$this->setDateItems(
				$this->_getPostDateDataAsArray()
			);
		}
		
		return $this->_getData('date_items');
	}

	/**
	 * Get the post date data as an array
	 *
	 * @return array
     * @throws \Exception
	 */
	protected function _getPostDateDataAsArray()
	{
		$days = $this->factory->create('Model\ResourceModel\Post')->getPostsOnDayByYearMonth($this->getYear() . '-' . $this->getMonth() . '-%');		

		$itemsByDay = array_combine(range(1, $this->getDaysInMonth()), range(1, $this->getDaysInMonth()));

		foreach($days as $day) {
			$itemsByDay[ltrim($day, '0')] = sprintf('<a href="%s">%s</a>', $this->url->getUrl($this->getYear() . '/' . $this->getMonth() . '/' . $day), $day);
		}

		$itemsByDay = array_values($itemsByDay);
		
		// Pad start of array
		$firstDayOfMonth = date('N', strtotime($this->getYear() . '-' . $this->getMonth() . '-01'));
		$itemsByDay = array_pad($itemsByDay, -(count($itemsByDay)+($firstDayOfMonth-1)) , null);

		// Pad end of array
		$lastDayOfMonth = date('t', strtotime($this->getYear() . '-' . $this->getMonth() . '-01'));
		$endOfMonthPadding = 7 - date('N', strtotime($this->getYear() . '-' . $this->getMonth() . '-' . $lastDayOfMonth));
		$itemsByDay = array_pad($itemsByDay, count($itemsByDay)+$endOfMonthPadding , null);

		$items = array();
		
		while(count($itemsByDay) > 0) {
			if (count($itemsByDay) >= 7) {
				$items[] = array_splice($itemsByDay, 0, 7, null);
			}
			else {
				$items[] = $itemsByDay;
				$itemsByDay = array();
			}
		}

		return $items;
	}
	
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		parent::_beforeToHtml();
		
		$this->_initDate();
		$this->_initPreviousNextLinks();

		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/calendar.phtml');
		}

		return $this;
	}
	
	/**
	 * Initialise the date block's date
	 * If no archive, use current date
	 *
	 * @return $this
     * @throws \Exception
	 */
	protected function _initDate()
	{
		if (($archive = $this->_registry->registry('wordpress_archive')) !== null) {
			$this->setYear($archive->getDatePart('Y'));
			$this->setMonth($archive->getDatePart('m'));
			$this->setDaysInMonth($archive->getDatePart('t'));

			$this->setDefaultTitle($archive->getDatePart('F, Y'));
			
			return $this;
		}

		$this->setYear(date('Y'));
		$this->setMonth(date('m'));
		$this->setDaysInMonth(date('t'));

		$this->setDefaultTitle($this->dateHelper->formatDate(date('Y-m-d 00:00:00', time()), 'F Y'));
		
		return $this;
	}
	
	/**
	 * Setup the previous and next links (if available)
	 *
	 * @return $this
     * @throws \Exception
	 */
	protected function _initPreviousNextLinks()
	{
		$posts = $this->factory->create('Model\ResourceModel\Post\Collection')
			->addIsViewableFilter()
			->setOrderByPostDate('desc')
			->addFieldToFilter('post_date', array('lteq' => $this->getYear() . '-' . $this->getMonth() . '-01 00:00:00'))
			->setPageSize(1)
			->setCurPage(1)
			->load();
			
		if (count($posts)) {
			$previous = $posts->getFirstItem();

			$this->setPreviousUrl($this->url->getUrl($previous->getPostDate('Y') . '/' . $previous->getPostDate('m') . '/'));
			$this->setPreviousText($previous->getPostDate('M'));
		}

		$dateString = date('Y-m-d', strtotime('+1 month', strtotime($this->getYear() . '-' . $this->getMonth() . '-01')));

		$posts = $this->_factory->getFactory('Post')->create()->getCollection()
			->addIsViewableFilter()
			->setOrderByPostDate('asc')
			->addFieldToFilter('post_date', array('gteq' => $dateString))
			->setPageSize(1)
			->setCurPage(1)
			->load();
			
		if (count($posts)) {
			$next = $posts->getFirstItem();

			$this->setNextUrl($this->url->getUrl($next->getPostDate('Y') . '/' . $next->getPostDate('m') . '/'));
			$this->setNextText($next->getPostDate('M'));
		}
		
		return $this;
	}
}
