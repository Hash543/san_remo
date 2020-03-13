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
namespace PSS\WordPress\Block\Archive;

class View extends \PSS\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	/**
	 * @return \PSS\WordPress\Model\Archive
	**/
	public function getEntity()
	{
		return $this->getArchive();
	}
	
	/**
	 * Caches and returns the archive model
	 *
	 * @return \PSS\WordPress\Model\Archive
	 */
	public function getArchive()
	{
	    try {
            if (!$this->hasArchive()) {
                $this->setArchive($this->registry->registry('wordpress_archive'));
            }
        }catch (\Exception $exception) {

        }
		return $this->_getData('archive');
	}

	/**
	 * Retrieve the Archive ID
	 *
	 * @return false|int
	 */
	public function getArchiveId()
	{
		if ($archive = $this->getArchive()) {
			return $archive->getId();
		}
		
		return false;
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
     * @throws \Exception
	 */
	protected function _getPostCollection()
	{
		$postCollection = parent::_getPostCollection()->addPostTypeFilter('post');
		if ($this->getArchive()) {
			$postCollection->addArchiveDateFilter($this->getArchiveId(), $this->getArchive()->getIsDaily());
		} else {
			$postCollection->forceEmpty();
		}
		return $postCollection;
	}

	/**
	 * Split a date by spaces and translate
	 *
	 * @param string $date
	 * @param string $splitter = ' '
	 * @return string
	 */
	public function translateDate($date, $splitter = ' ')
	{
		return $this->wpContext->getDateHelper()->translateDate($date, $splitter);
	}
}
