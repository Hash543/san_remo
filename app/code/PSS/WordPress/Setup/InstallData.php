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
namespace PSS\WordPress\Setup;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface {

    private $_associationTypeFactory;
    /**
     * Init
     *
     * @param \PSS\WordPress\Model\AssociationTypeFactory $associationTypeFactory
     */
    public function __construct(
        \PSS\WordPress\Model\AssociationTypeFactory $associationTypeFactory) {
        $this->_associationTypeFactory = $associationTypeFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context) {
         $registers = [
             [
                 'object' => 'product',
                 'wordpress_object' => 'post'
             ],
             [
                 'object' => 'product',
                 'wordpress_object' => 'category'
             ],
             [
                 'object' => 'category',
                 'wordpress_object' => 'post'
             ],
             [
                 'object' => 'category',
                 'wordpress_object' => 'category'
             ],
             [
                 'object' => 'cms_page',
                 'wordpress_object' => 'post'
             ],
             [
                 'object' => 'cms_page',
                 'wordpress_object' => 'category'
             ]
         ];
         foreach ($registers as $registry ) {
             try {
                 $this->getNewAssociationType()->setData($registry)->save();
             } catch (\Exception $exception) {
                 //Todo: there is not logger added yet
             }
         }
    }

    /**
     * @return \PSS\WordPress\Model\AssociationType
     */
    private function getNewAssociationType(){
        return $this->_associationTypeFactory->create();
    }
}