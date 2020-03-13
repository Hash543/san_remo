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
namespace PSS\WordPress\Model;

class Factory
{
	/**
	 * @var array
	 */
	protected $factories = [];
	
	/**
	 * Create an instance of $type
	 *
	 * @param  string $type
     * @param array $args
	 * @return object
	 */
	public function create($type, array $args = [])
	{
		if ($className = $this->getClassNameFromType($type)) {
			return $this->getObjectManager()->create($className, $args);
		}
		return false;
	}
	
	/*
	 *
	 *
	 * @param  string $type
	 * @return object|false
	 */
	public function get($type)
	{
		if ($className = $this->getClassNameFromType($type)) {
			return $this->getObjectManager()->get($className);
		}
		
		return false;
	}

	/*
	 *
	 *
	 * @param  string $type
	 * @return object|false
	 */
	protected function getObjectManager()
	{
		return \Magento\Framework\App\ObjectManager::getInstance();
	}

	/*
	 *
	 *
	 * @param  string $type
	 * @return string
	 */
	protected function getClassNameFromType($type)
	{
		if (trim($type) === '') {
			return false;
		}

		if (strpos($type, 'PSS') === 0) {
			return $type;
		}

		$type   = trim($type, '\\');
		$prefix = __NAMESPACE__ . '\\';
		
		if (strpos($type, '\\') > 0) {
			$prefix = 'PSS\WordPress\\';
		}

		return $prefix . $type;
	}
}
