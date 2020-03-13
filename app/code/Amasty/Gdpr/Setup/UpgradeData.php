<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup;

use Amasty\Gdpr\Model\Config;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\PolicyFactory;
use Amasty\Gdpr\Model\Policy;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var PolicyFactory
     */
    private $policyFactory;

    public function __construct(
        PageFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        WriterInterface $configWriter,
        TypeListInterface $typeList,
        ConfigInterface $resourceConfig,
        PolicyRepositoryInterface $policyRepository,
        PolicyFactory $policyFactory
    ) {
        $this->configWriter = $configWriter;
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->typeList = $typeList;
        $this->resourceConfig = $resourceConfig;
        $this->policyRepository = $policyRepository;
        $this->policyFactory = $policyFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.5', '<')) {
            $path = 'amasty_gdpr/deletion_notification/';
            $fields = ['sender', 'reply_to', 'template'];
            $connection = $this->resourceConfig->getConnection();

            foreach ($fields as $key => $fieldId) {
                $data = ['path' => $path . 'deny_' . $fieldId];
                $whereCondition = ['path = ?' => $path . $fieldId];
                $connection->update($this->resourceConfig->getMainTable(), $data, $whereCondition);
            }
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.2.0', '<')) {
            if (!$this->policyRepository->getCurrentPolicy()) {
                $policyData = [
                    'comment' => 'Automatically generated during installation',
                    'policy_version' => 'v1.0',
                    'status' => Policy::STATUS_ENABLED,
                    'content' => 'This is the privacy policy sample page.
<br>It was created automatically and do not substitute the one you need to create and provide to your store visitors. 
<br>Please, replace this text with the correct privacy policy by visiting the Customers > GDPR > Privacy Policy section in the backend.'
                ];

                /** @var Policy $policyModel */
                $policyModel = $this->policyFactory->create();
                $policyModel->addData($policyData);
                $policyModel->setLastEditedBy(null);
                $this->policyRepository->save($policyModel);
            }
        }
    }
}
