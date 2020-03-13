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

use PSS\WordPress\Controller\Action;
use Magento\Framework\Controller\ResultFactory;

class View extends Action
{
    /**
     * @return bool|\PSS\WordPress\Model\Post
     */
    protected function _getEntity()
    {
        $post = $this->factory->create('Post')->load(
            (int)$this->getRequest()->getParam('id')
        );

        if (!$post->getId()) {
            return false;
        }

        return $post;
    }

    /**
     * @return bool
     */
    protected function _canPreview()
    {
        return true;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getForward()
    {
        if ($entity = $this->_getEntity()) {
            if ($entity->isFrontPage()) {
                if ((int)$this->getRequest()->getParam('is_front') === 0) {
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($this->url->getHomeUrl());
                } else {
                    // Request is static homepage (page) with a preview set (maybe visual editor)
                    foreach(['p', 'page_id', 'preview_id'] as $paramKey) {
                        if ($previewId = (int)$this->getRequest()->getParam($paramKey)) {
                            $previewPost = $this->factory->create('Post')->load($previewId);
                            if ($previewPost->getId() && !$previewPost->isPublished()) {
                                $this->getRequest()->setParam('preview_id', $previewPost->getId());
                                $this->registry->unregister($previewPost::ENTITY);
                                return $this->resultFactory
                                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
                                    ->setModule('wordpress')
                                    ->setController('post')
                                    ->forward('preview');
                            }
                        }
                    }
                }
            }
            if ($entity->getPostStatus() === 'private' && !$this->wpContext->getCustomerSession()->isLoggedIn()) {
                return $this->_getNoRouteForward();
            }
        }
        return parent::_getForward();
    }

    /**
     * @return $this|Action
     */
    protected function _initLayout()
    {
        parent::_initLayout();

        if ($commentId = (int)$this->getRequest()->getParam('comment-id')) {
            $commentStatus = (int)$this->getRequest()->getParam('comment-status');

            if ($commentStatus === 0) {
                $this->messageManager->addSuccess(__('Your comment has been posted and is awaiting moderation.'));
            } else {
                $this->messageManager->addSuccess(__('Your comment has been posted.'));
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        if ($this->_getEntity()->isFrontPage()) {
            return [];
        }

        $crumbs = parent::_getBreadcrumbs();

        // Handle post type breadcrumb
        $postType = $this->getEntityObject()->getTypeInstance();

        if ($crumbObjects = $postType->getBreadcrumbStructure($this->getEntityObject())) {
            foreach ($crumbObjects as $crumbType => $crumbObject) {
                $crumbs[$crumbType] = [
                    'label' => __($crumbObject->getName()),
                    'title' => __($crumbObject->getName()),
                    'link' => $crumbObject->getUrl(),
                ];
            }
        }

        $crumbs['post'] = [
            'label' => __($this->_getEntity()->getName()),
            'title' => __($this->_getEntity()->getName())
        ];

        return $crumbs;
    }

    /**
     * @return array
     */
    public function getLayoutHandles()
    {
        $post = $this->getEntityObject();
        $postType = $post->getPostType();

        if ($postType == 'revision' && $post->getParentPost()) {
            $postType = $post->getParentPost()->getPostType();
            $template = $post->getParentPost()->getMetaValue('_wp_page_template');
        } else {
            $template = $post->getMetaValue('_wp_page_template');
        }

        $layoutHandles = ['wordpress_post_view_default'];

        if ($post->isFrontPage()) {
            $layoutHandles[] = 'wordpress_front_page';
        }

        $layoutHandles[] = 'wordpress_' . $postType . '_view';
        $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $post->getId();

        if ($template) {
            $templateName = str_replace('.php', '', $template);
            $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName;
            $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName . '_' . $post->getId();
            if ($postType !== 'post') {
                $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName;
                $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName . '_' . $post->getId();
            }
        }

        return array_merge(parent::getLayoutHandles(), $layoutHandles);
    }
}
