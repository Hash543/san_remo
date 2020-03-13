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

namespace PSS\WordPress\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as Context;
use PSS\WordPress\Model\WidgetManager;
use PSS\WordPress\Model\OptionManager;
use PSS\WordPress\Model\Plugin;
use Magento\Framework\Registry;

class Sidebar extends Template
{
    /**
     * @var WidgetManager
     */
    protected $widgetManager;

    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @var Plugin
     */
    protected $plugin;
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Sidebar constructor.
     * @param Context $context
     * @param WidgetManager $widgetManager
     * @param OptionManager $optionManager
     * @param Plugin $plugin
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        WidgetManager $widgetManager,
        OptionManager $optionManager,
        Plugin $plugin,
        Registry $registry,
        array $data = []
    ) {
        $this->widgetManager = $widgetManager;
        $this->optionManager = $optionManager;
        $this->plugin = $plugin;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Load all enabled widgets
     *
     * @return \PSS\WordPress\Block\Sidebar
     */
    protected function _beforeToHtml()
    {
        if ($widgets = $this->getWidgetsArray()) {
            foreach ($widgets as $widgetType) {
                if ($block = $this->widgetManager->getWidget($widgetType)) {
                    $this->setChild('wordpress_widget_' . $widgetType, $block);
                }
            }
        }

        if (!$this->getTemplate()) {
            $this->setTemplate('sidebar.phtml');
        }

        return parent::_beforeToHtml();
    }

    /**
     * Get the widget area
     * Set a custom widget area by calling $this->setWidgetArea('your-custom-area')
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWidgetArea()
    {
        if (!$this->hasWidgetArea()) {
            $this->setData('widget_area', 'sidebar-main');
        }

        return $this->_getData('widget_area');
    }

    /**
     * Set the widget area
     * @param string $widgetArea
     * @return Sidebar
     */
    public function setWidgetArea($widgetArea)
    {
        return $this->setData('widget_area', $widgetArea);
    }

    /**
     * Retrieve the sidebar widgets as an array
     *
     * @return false|array
     * @throws \Exception
     */
    public function getWidgetsArray()
    {
        if ($this->getWidgetArea()) {
            $widgets = $this->optionManager->getOption('sidebars_widgets');

            if ($widgets) {
                $widgets = unserialize($widgets);

                $realWidgetArea = $this->getRealWidgetArea();

                if (isset($widgets[$realWidgetArea])) {
                    return $widgets[$realWidgetArea];
                }
            }
        }

        return false;
    }

    /**
     * Get the real widget area by using the Custom Sidebars plugin
     *
     * @return string
     * @throws \Exception
     */
    public function getRealWidgetArea()
    {
        if (!$this->plugin->isEnabled('custom-sidebars/customsidebars.php')) {
            return $this->getWidgetArea();
        }

        if (!($settings = @unserialize($this->optionManager->getOption('cs_modifiable')))) {
            return $this->getWidgetArea();
        }

        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (!isset($settings['modifiable']) || array_search($this->getWidgetArea(), $settings['modifiable']) === false) {
            return $this->getWidgetArea();
        }

        if ($post = $this->registry->registry('wordpress_post')) {
            if ($value = $post->getMetaValue('_cs_replacements')) {
                $value = @unserialize($value);

                if (isset($value[$this->getWidgetArea()])) {
                    return $value[$this->getWidgetArea()];
                }
            }

            # Single post by type
            if ($widgetArea = $this->_getArrayValue($settings, 'post_type_single/' . $post->getPostType() . '/' . $this->getWidgetArea())) {
                return $widgetArea;
            }

            # Single post by category
            if ($categoryIdResults = $post->getResource()->getParentTermsByPostId($post->getId(), $taxonomy = 'category')) {
                $categoryIdResults = array_pop($categoryIdResults);

                if (isset($categoryIdResults['category_ids'])) {
                    foreach (explode(',', $categoryIdResults['category_ids']) as $categoryId) {
                        if ($widgetArea = $this->_getArrayValue($settings, 'category_single/' . $categoryId . '/' . $this->getWidgetArea())) {
                            return $widgetArea;
                        }
                    }
                }
            }
        } else if ($postType = $this->registry->registry('wordpress_post_type')) {
            if (isset($settings['post_type_archive'][$postType->getPostType()][$this->getWidgetArea()])) {
                return $settings['post_type_archive'][$postType->getPostType()][$this->getWidgetArea()];
            }
        } else if ($term = $this->registry->registry('wordpress_term')) {
            if ($widgetArea = $this->_getArrayValue($settings, $term->getTaxonomy() . '_archive/' . $term->getId() . '/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        } else if (in_array('wordpress_homepage', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'blog/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        } else if ($author = $this->registry->registry('wordpress_author')) {
            if ($widgetArea = $this->_getArrayValue($settings, 'authors/' . $author->getId() . '/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        } else if (in_array('wordpress_search_index', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'search/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        } else if (in_array('wordpress_archive_view', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'date/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        } else if (in_array('wordpress_post_tag_view', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'tags/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        }

        return $this->getWidgetArea();
    }

    /**
     * Retrieve a deep value from a multideimensional array
     *
     * @param array $arr
     * @param string $key
     * @return string|null
     */
    protected function _getArrayValue($arr, $key)
    {
        $keys = explode('/', trim($key, '/'));

        foreach ($keys as $key) {
            if (!isset($arr[$key])) {
                return null;
            }

            $arr = $arr[$key];
        }

        return $arr;
    }

    /**
     * Determine whether or not to display the sidebar
     *
     * @return int
     */
    public function canDisplay()
    {
        return 1;
    }
}
