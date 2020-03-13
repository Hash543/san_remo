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

namespace PSS\WordPress\Controller\Post;

class Preview extends View
{
    /**
     * Load and return a Post model
     * @return bool|\PSS\WordPress\Model\Post
     */
    protected function _getEntity()
    {
        $post = $this->factory->create('Post')->load(
            (int)$this->getRequest()->getParam('preview_id')
        );

        if (!$post->getId()) {
            return false;
        }
        return ($revision = $post->getLatestRevision()) ? $revision : $post;
    }

    /**
     * @return bool
     */
    protected function _getForward()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function _canPreview()
    {
        return false;
    }
}
