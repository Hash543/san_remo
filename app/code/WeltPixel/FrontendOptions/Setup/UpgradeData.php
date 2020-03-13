<?php
namespace WeltPixel\FrontendOptions\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    private $blockFactory;

    public function __construct(BlockFactory $blockFactory, State $appState)
    {
        $this->blockFactory = $blockFactory;
        $this->appState = $appState;

    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            if(!$this->appState->isAreaCodeEmulated()) {
                $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            }
        } catch (\Exception $ex) {}
        $setup->startSetup();

        $connection = $setup->getConnection();
        $configDataTable = $setup->getTable('core_config_data');

        if ( version_compare( $context->getVersion(), '1.1.1' ) < 0 ) {
            $connection->delete($configDataTable, '`path` LIKE "%weltpixel_frontend_options/paragraph%"');
        }

        if (version_compare($context->getVersion(), "1.1.2", "<")) {
            $content = '<h1 class="contact-details">Contact details</h1>
                        <div class="container-wpx-contact">
                        <div class="col-md-4"><i class="icon-phone icon-4x"></i>
                        <div class="details">
                        <h4>Phone</h4>
                        <p>PHONE 01: +84 01122000</p>
                        </div>
                        </div>
                        <div class="col-md-4 border-contact"><i class="icon-location icon-4x"></i>
                        <div class="details">
                        <h4>Adress</h4>
                        <p>1800 Abbot Kinney Blvd.<br />Unit D, Venice</p>
                        </div>
                        </div>
                        <div class="col-md-4"><i class="icon-email3 icon-4x"></i>
                        <div class="details">
                        <h4>Email</h4>
                        <p>apprilstore@gmail.com<br />support@april.com</p>
                        </div>
                        </div>
                        </div>
                        <h1 class="title-contact text-center">Get in touch with us</h1>
                        <p class="text-center mob-pd">Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur,</p>
                        <p class="text-center mob-pd">vel illum qui dolorem eum fugiat quo voluptas nulla pariatur erit qui inea</p>';
            $cmsBlockData = [
                'title' => 'Pearl Contact CMS block',
                'identifier' => 'pearl_contact_cms_block',
                'content' => $content,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            $this->blockFactory->create()->setData($cmsBlockData)->save();
        }

        $setup->endSetup();
    }
}
