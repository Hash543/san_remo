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
namespace PSS\WordPress\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Addon extends Field
{
	/**
	 * @var ModuleListInterface
	 */
	protected $moduleList;

    /**
     * Addon constructor.
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param array $data
     */
	public function __construct(Context $context, ModuleListInterface $moduleList, array $data = [])
	{
		parent::__construct($context, $data);
		
		$this->moduleList = $moduleList;	
	}
	
	/**
	 *
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	protected function _getElementHtml(AbstractElement $element)
	{
		$addonModule = trim(str_replace('wordpress_addon_Pss_', '', $element->getId()));
		$moduleInfo  = $this->moduleList->getOne('PSS_' . $addonModule);
		
		$configBlock = \Magento\Framework\App\ObjectManager::getInstance()
			->create('PSS\\' . $addonModule . '\Block\Adminhtml\System\Config\Form\Field\Addon');

		if (isset($moduleInfo['setup_version'])) {
			$configBlock->setModuleVersion($moduleInfo['setup_version']);
		}
		
		return $configBlock->render($element);
	}

	/**
	 *
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	protected function _renderScopeLabel(AbstractElement $element)
	{
		return '';
	}

	/**
	 *
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		return str_replace('class="label"', 'style="vertical-align: middle;" class="label"', parent::render($element));
	}
}
