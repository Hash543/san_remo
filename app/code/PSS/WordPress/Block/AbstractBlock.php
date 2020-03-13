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

/* Constructor */
use Magento\Framework\View\Element\Template\Context;
use PSS\WordPress\Model\Context as WPContext;

abstract class AbstractBlock extends Template
{
    /**
     * @var WPContext
     */
	protected $wpContext;
    /**
     * @var \PSS\WordPress\Model\OptionManager
     */
	protected $optionManager;
    /**
     * @var \PSS\WordPress\Model\ShortcodeManager
     */
	protected $shortcodeManager;
    /**
     * @var \Magento\Framework\Registry
     */
	protected $registry;

    /**
     * @var \PSS\WordPress\Model\Url
     */
	protected $url;

    /**
     * @var \PSS\WordPress\Model\Factory
     */
	protected $factory;

    /**
     * AbstractBlock constructor.
     * @param Context $context
     * @param WPContext $wpContext
     * @param array $data
     */
    public function __construct(Context $context, WPContext $wpContext, array $data = [])
    {
        $this->wpContext = $wpContext;
        $this->optionManager = $wpContext->getOptionManager();
        $this->shortcodeManager = $wpContext->getShortcodeManager();
        $this->registry = $wpContext->getRegistry();
        $this->url = $wpContext->getUrl();
        $this->factory = $wpContext->getFactory();

        parent::__construct($context, $data);
    }


    /**
     * Parse and render a shortcode
     * @param string $shortcode
     * @param null $object
     * @return string
     */
    public function renderShortcode($shortcode, $object = null)
    {
        return $this->shortcodeManager->renderShortcode($shortcode, ['object' => $object]);
    }

    /**
     * @param $shortcode
     * @param null $object
     * @return string
     */
    public function doShortcode($shortcode, $object = null)
    {
        return $this->renderShortcode($shortcode, $object);
    }
    /**
	 *
	 * @return \PSS\WordPress\Model\Factory
	 */
    public function getFactory()
    {
        return $this->factory;
    }
}
