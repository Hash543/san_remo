<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Setup;

use Amasty\Rules\Helper\Data;
use Amasty\Rules\Model\CheckEnterprise;
use Amasty\Rules\Model\ResourceModel\RuleFactory as RuleResourceFactory;
use Amasty\Rules\Model\RuleFactory as AmastyRule;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Data\Rule;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RuleResourceFactory
     */
    private $ruleResourceFactory;

    /**
     * @var AmastyRule
     */
    private $ruleFactory;

    /**
     * @var CheckEnterprise
     */
    private $checkEnterprise;

    public function __construct(
        State $appState,
        RuleRepositoryInterface $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RuleResourceFactory $ruleResourceFactory,
        AmastyRule $ruleFactory,
        CheckEnterprise $checkEnterprise
    ) {
        try {
            $appState->getAreaCode();
        } catch (\Exception $e) {
            $appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ruleRepository = $ruleRepository;
        $this->ruleResourceFactory = $ruleResourceFactory;
        $this->ruleFactory = $ruleFactory;
        $this->checkEnterprise = $checkEnterprise;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addAmrulesTable($setup);
        }
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->migrateRules($setup);
        }
        if (version_compare($context->getVersion(), '2.1.1', '<')
            && $this->checkEnterprise->isEnterprise()
        ) {
            $this->scheduleRules($setup);
        }

        if (version_compare($context->getVersion(), '2.2.3', '<')) {
            $this->addApplyDiscountTo($setup);
        }

        if (version_compare($context->getVersion(), '2.2.4', '<')) {
            $this->addUseFor($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    private function migrateRules(SchemaSetupInterface $setup)
    {
        $rulesArray = [
            Data::TYPE_XY_PERCENT,
            Data::TYPE_XY_FIXED,
            Data::TYPE_XY_FIXDISC,
            Data::TYPE_AFTER_N_DISC,
            Data::TYPE_AFTER_N_FIXDISC,
            Data::TYPE_AFTER_N_FIXED,
        ];

        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(Rule::KEY_SIMPLE_ACTION, $rulesArray, 'in')->create();

        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rulesCollection */
        $rulesCollection = $this->ruleRepository->getList($searchCriteria);
        $rules = $rulesCollection->getItems();

        /** @var \Magento\SalesRule\Model\Data\Rule $rule */
        foreach ($rules as $rule) {
            $action = $rule->getSimpleAction();
            $discountStep = $rule->getDiscountStep();
            switch ($action) {
                case Data::TYPE_XY_PERCENT:
                    $rule->setSimpleAction(Data::TYPE_XN_PERCENT);
                    break;
                case Data::TYPE_XY_FIXED:
                    $rule->setSimpleAction(Data::TYPE_XN_FIXED);
                    break;
                case Data::TYPE_XY_FIXDISC:
                    $rule->setSimpleAction(Data::TYPE_XN_FIXDISC);
                    break;
                case Data::TYPE_AFTER_N_DISC:
                    $rule->setSimpleAction(Data::TYPE_EACH_M_AFT_N_PERC);
                    $rule->setDiscountStep(1);
                    break;
                case Data::TYPE_AFTER_N_FIXDISC:
                    $rule->setSimpleAction(Data::TYPE_EACH_M_AFT_N_DISC);
                    $rule->setDiscountStep(1);
                    break;
                case Data::TYPE_AFTER_N_FIXED:
                    $rule->setSimpleAction(Data::TYPE_EACH_M_AFT_N_FIX);
                    $rule->setDiscountStep(1);
                    break;
            }
            $this->ruleRepository->save($rule);
            $this->setNqtyToXYrules($rule, $discountStep);
        }
    }

    /**
     * @param $rule
     * @param $discountStep
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function setNqtyToXYrules($rule, $discountStep)
    {
        if ($rule->getRuleId()) {
            /** @var \Amasty\Rules\Model\Rule $amastyRule */
            $amastyRule = $this->ruleFactory->create();
            /** @var \Amasty\Rules\Model\ResourceModel\Rule $ruleResource */
            $ruleResource = $this->ruleResourceFactory->create();
            $ruleResource->load($amastyRule, $rule->getRuleId(), 'salesrule_id');
            if (in_array($rule->getSimpleAction(), Data::BUY_X_GET_Y)) {
                $amastyRule->setNqty(1);
            } elseif (in_array($rule->getSimpleAction(), Data::TYPE_EACH_M_AFT_N)) {
                $amastyRule->setEachm($discountStep);
            }
            $ruleResource->save($amastyRule);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    protected function addAmrulesTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'amasty_amrules_rule'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_amrules_rule'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'salesrule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Salesrule Entity Id'
            )
            ->addColumn(
                'eachm',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Each M Product'
            )
            ->addColumn(
                'priceselector',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Price Base On'
            )
            ->addColumn(
                'promo_cats',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Additional Y cats'
            )
            ->addColumn(
                'promo_skus',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Additional Y skus'
            )
            ->addColumn(
                'nqty',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'N Qty'
            )
            ->addColumn(
                'skip_rule',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Skip Rule'
            )
            ->addColumn(
                'max_discount',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Max Discount Amount'
            )
            ->addIndex(
                $setup->getIdxName('amasty_amrules_rule', ['salesrule_id']),
                ['salesrule_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amrules_rule',
                    'salesrule_id',
                    'salesrule',
                    'rule_id'
                ),
                'salesrule_id',
                $setup->getTable('salesrule'),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Amasty Promotions Rules Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return UpgradeSchema
     */
    private function scheduleRules(SchemaSetupInterface $setup)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $setup->getConnection();
        $adapter->dropForeignKey(
            $setup->getTable('amasty_amrules_rule'),
            $setup->getFkName(
                'amasty_amrules_rule',
                'salesrule_id',
                'salesrule',
                'rule_id'
            )
        );
        $adapter->addForeignKey(
            $setup->getFkName(
                'amasty_amrules_rule',
                'salesrule_id',
                'salesrule',
                'row_id'
            ),
            $setup->getTable('amasty_amrules_rule'),
            'salesrule_id',
            $setup->getTable('salesrule'),
            'row_id'
        );

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addApplyDiscountTo(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amrules_rule'),
            'apply_discount_to',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 4,
                'nullable' => false,
                'comment' => 'Apply Discount To'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addUseFor(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amrules_rule'),
            'use_for',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => 4,
                'nullable' => false,
                'comment' => 'Use'
            ]
        );
    }

}
