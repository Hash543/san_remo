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
namespace PSS\WordPress\Controller\Homepage;

use PSS\WordPress\Controller\Action;

class View extends Action
{
    /**
     * @return mixed
     */
	protected function _getEntity()
	{
		return $this->factory->get('Homepage');
	}

    /**
     * @return bool
     */
	protected function _canPreview()
	{
		return true;
	}

    /**
     * Get the blog breadcrumbs
     * @return array
     */
	protected function _getBreadcrumbs()
	{
		$crumbs = parent::_getBreadcrumbs();
		
		if ($this->url->isRoot()) {
			$crumbs['blog'] = [
				'label' => __($this->_getEntity()->getName()),
				'title' => __($this->_getEntity()->getName())
			];
		}
		else {
			unset($crumbs['blog']['link']);
		}

		return $crumbs;
	}

    /**
     * Set the 'wordpress_front_page' handle if this is the front page
     * @return array
     */
	public function getLayoutHandles()
	{
		$handles = ['wordpress_homepage_view'];
		
		if (!$this->_getEntity()->getStaticFrontPageId()) {
			$handles[] = 'wordpress_front_page';
		}
		
		return array_merge($handles, parent::getLayoutHandles());
	}
}
