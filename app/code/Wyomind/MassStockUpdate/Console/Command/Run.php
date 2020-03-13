<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * $ bin/magento help wyomind:massstockupdate:run
 * Usage:
 * wyomind:massstockupdate:run [profile_id1] ... [profile_idN]
 *
 * Arguments:
 * profile_id            Space-separated list of import profiles (run all profiles if empty)
 *
 * Options:
 * --help (-h)           Display this help message
 * --quiet (-q)          Do not output any message
 * --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * --version (-V)        Display this application version
 * --ansi                Force ANSI output
 * --no-ansi             Disable ANSI output
 * --no-interaction (-n) Do not ask any interactive question
 */
class Run extends Command
{
    public $module = "MassStockUpdate";
    public $name = "Mass Stock Update";

    const PROFILE_IDS_OPTION = "profile_ids";

    protected $_profilesCollectionFactory = null;
    protected $_loggerFactory = null;
    protected $_state = null;

    public function __construct(
        \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $profilesCollectionFactory,
        \Wyomind\MassStockUpdate\Logger\LoggerFactory $loggerFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->_state = $state;
        $this->_profilesCollectionFactory = $profilesCollectionFactory;
        $this->_loggerFactory = $loggerFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:' . strtolower($this->module) . ':run')
            ->setDescription(__('Run ' . $this->name . ' profiles'))
            ->setDefinition([
                new \Symfony\Component\Console\Input\InputOption(
                    self::PROFILE_IDS_OPTION,
                    'p',
                    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL | \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY,
                    __('List of profiles to run (comma separated)')
                )
            ]);
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;

        try {
            $this->_state->setAreaCode('adminhtml');
            $profilesIds = array_filter($input->getOption(self::PROFILE_IDS_OPTION));

            if (!is_array($profilesIds) || !count($profilesIds)) {
                throw new \InvalidArgumentException('--profile_ids is empty. Please specify the profile to run.');
            }

            $collection = $this->_profilesCollectionFactory->create()->getList($profilesIds);

            $logger = $this->_loggerFactory->create();

            foreach ($collection as $profile) {

                $logger->notice("");
                $logger->notice(__("~~~ Run profile #%1 : %2 ~~~", $profile->getId(), $profile->getName()));
                $logger->notice("");

                $profile->multipleImport();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return $returnValue;
    }
}