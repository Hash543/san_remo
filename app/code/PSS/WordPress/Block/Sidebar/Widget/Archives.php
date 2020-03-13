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

class Archives extends AbstractWidget
{
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
	protected $archiveCollection;
    /**
     * Returns a collection of valid archive dates
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
	public function getArchives()
	{
		if (is_null($this->archiveCollection)) {
			$dates = $this->factory->create('Model\ResourceModel\Archive')->getDatesForWidget();
			$archiveCollection = array();
			
			foreach($dates as $date) {
				$archiveCollection[] = $this->factory->create('Model\ArchiveFactory')->create()->load($date['archive_date'])->setPostCount($date['post_count']);
			}

			$this->archiveCollection = $archiveCollection;
		}
		
		return $this->archiveCollection;
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
     * Determine whether the archive is the current archive
     * @param \PSS\WordPress\Model\Archive $archive
     * @return bool
     */
	public function isCurrentArchive($archive)
	{
		if ($this->getCurrentArchive()) {
			return $archive->getId() == $this->getCurrentArchive()->getId();
		}
		return false;
	}

    /**
     * Retrieve the current archive
     * @return \PSS\WordPress\Model\Archive
     */
	public function getCurrentArchive() {
		return $this->registry->registry('wordpress_archive');
	}

    /**
     * Retrieve the default title
     * @return \Magento\Framework\Phrase|string
     */
	public function getDefaultTitle()
	{
		return __('Archives');
	}

    /**
     * @return \PSS\WordPress\Block\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/archives.phtml');
		}
		return parent::_beforeToHtml();
	}
}
