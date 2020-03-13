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
namespace PSS\WordPress\Model\ResourceModel;

/* Parent Class */
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/* Constructor Args */
use Magento\Framework\Model\ResourceModel\Db\Context;
use PSS\WordPress\Model\Context as WPContext;

abstract class AbstractResource extends AbstractDb
{
	/*
	 *
	 */
	protected $wpContext;

	/*
	 *
	 */
	protected $resourceConnection = null;
	
	/*
	 *
	 *
	 * @return
	 */
	public function __construct(
      Context $context,
		WPContext $wpContext,
              $connectionName = null
  )
	{
		$this->wpContext          = $wpContext;
		$this->factory            = $wpContext->getFactory();
		$this->resourceConnection = $wpContext->getResourceConnection();

		parent::__construct($context, $connectionName);
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getConnection()
	{
		return $this->resourceConnection->getConnection();
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getTable($tableName)
	{
		return $this->resourceConnection->getTable($tableName);;
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getTablePrefix()
	{
		return $this->resourceConnection->getTablePrefix();
	}
}
