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
use Magento\Framework\App\Helper\Context;
use PSS\WordPress\Model\OptionManager;

class Date extends AbstractHelper
{
    /**
     * @var OptionManager
     */
	protected $optionManager;

    /**
     * Date constructor.
     * @param Context $context
     * @param OptionManager $optionManager
     */
	public function __construct(Context $context, OptionManager $optionManager)
	{
		parent::__construct($context);
		
		$this->optionManager = $optionManager;
	}

    /**
     * Formats a Wordpress date string
     * @param $date
     * @param null $format
     * @param bool $f
     * @return string
     */
	public function formatDate($date, $format = null, $f = false)
	{
		if ($format == null) {
			$format = $this->getDefaultDateFormat();
		}
		
		/**
		 * This allows you to translate month names rather than whole date strings
		 * eg. "March","Mars"
		 *
		 */
		$len = strlen($format);
		$out = '';
		
		for( $i = 0; $i < $len; $i++) {	
			$out .= __(date($format[$i], strtotime($date)));
		}
		
		return $out;
	}

    /**
     *  Formats a Wordpress date string
     * @param $time
     * @param null $format
     * @return string
     */
	public function formatTime($time, $format = null)
	{
		if ($format == null) {
			$format = $this->getDefaultTimeFormat();
		}
		
		return $this->formatDate($time, $format);
	}

    /**
     * Split a date by spaces and translate
     * @param $date
     * @param string $splitter
     * @return string
     */
	public function translateDate($date, $splitter = ' ')
	{
		$dates = explode($splitter, $date);
		
		foreach($dates as $it => $part) {
			$dates[$it] = __($part);
		}
		
		return implode($splitter, $dates);
	}

    /**
     * Return the default date formatting
     * @return mixed|string
     */
	public function getDefaultDateFormat()
	{
		if ($format = $this->optionManager->getOption('date_format')) {
			return $format;
		}
		
		return 'F jS, Y';
	}
	
	/**
	 * Return the default time formatting
	 * @return string
	 */
	public function getDefaultTimeFormat()
	{
		if ($format = $this->optionManager->getOption('time_format')) {
			return $format;
		}

		return 'g:ia';
	}
}
