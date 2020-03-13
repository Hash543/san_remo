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

class Archive extends \PSS\WordPress\Model\ResourceModel\AbstractResource
{
	/**
	 * Set the table and primary key
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('wordpress_post', 'ID');
	}
	
	public function getDatesForWidget()
	{
		return $this->getConnection()
			->fetchAll(
				"SELECT COUNT(ID) AS post_count, CONCAT(SUBSTRING(post_date, 1, 4), '/', SUBSTRING(post_date, 6, 2)) as archive_date 
					FROM `" . $this->getMainTable() . "` AS `main_table` WHERE (`main_table`.`post_type`='post') AND (`main_table`.`post_status` ='publish') 
					GROUP BY archive_date ORDER BY archive_date DESC"
			);
	}
}
