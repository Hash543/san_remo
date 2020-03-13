<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Setup;

use Amasty\GdprCookie\Setup\Operation\CreateCookieTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupsTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieDescriptionTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupDescriptionTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupLinkTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieConsentTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateCookieTable
     */
    private $cookieTable;

    /**
     * @var CreateCookieGroupsTable
     */
    private $cookieGroupsTable;

    /**
     * @var CreateCookieDescriptionTable
     */
    private $cookieDescriptionTable;

    /**
     * @var CreateCookieGroupDescriptionTable
     */
    private $cookieGroupDescriptionTable;

    /**
     * @var CreateCookieGroupLinkTable
     */
    private $cookieGroupLinkTable;

    /**
     * @var CreateCookieConsentTable
     */
    private $cookieConsentTable;

    public function __construct(
        CreateCookieTable $cookieTable,
        CreateCookieGroupsTable $cookieGroupsTable,
        CreateCookieDescriptionTable $cookieDescriptionTable,
        CreateCookieGroupDescriptionTable $cookieGroupDescriptionTable,
        CreateCookieGroupLinkTable $cookieGroupLinkTable,
        CreateCookieConsentTable $cookieConsentTable
    ) {
        $this->cookieTable = $cookieTable;
        $this->cookieGroupsTable = $cookieGroupsTable;
        $this->cookieDescriptionTable = $cookieDescriptionTable;
        $this->cookieGroupDescriptionTable = $cookieGroupDescriptionTable;
        $this->cookieGroupLinkTable = $cookieGroupLinkTable;
        $this->cookieConsentTable = $cookieConsentTable;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->cookieTable->execute($setup);
        $this->cookieGroupsTable->execute($setup);
        $this->cookieDescriptionTable->execute($setup);
        $this->cookieGroupDescriptionTable->execute($setup);
        $this->cookieGroupLinkTable->execute($setup);
        $this->cookieConsentTable->execute($setup);

        $setup->endSetup();
    }
}
