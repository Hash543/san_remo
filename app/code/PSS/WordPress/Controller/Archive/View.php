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

namespace PSS\WordPress\Controller\Archive;

use PSS\WordPress\Controller\Action;

class View extends Action
{
    /**
     *  Load the Archive model
     * @return \PSS\WordPress\Model\Archive
     */
    protected function _getEntity()
    {
        return $this->factory->create('Archive')->load(
            trim($this->_request->getParam('year') . '/' . $this->_request->getParam('month') . '/' . $this->_request->getParam('day'), '/')
        );
    }

    /**
     *  Get the blog breadcrumbs
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        return array_merge(
            parent::_getBreadcrumbs(), [
            'archives' => [
                'label' => __($this->_getEntity()->getName()),
                'title' => __($this->_getEntity()->getName())
            ]]);
    }
    /**
     * @return array
     */
    public function getLayoutHandles()
    {
        return array_merge(
            parent::getLayoutHandles(),
            ['wordpress_archive_view']
        );
    }
}
