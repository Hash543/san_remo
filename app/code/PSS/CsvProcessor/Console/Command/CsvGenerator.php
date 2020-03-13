<?php

namespace Pss\CsvProcessor\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class CsvGenerator extends Command
{
    /**
     * @var \Pss\CsvProcessor\Helper\CsvGenerator
     */
    private $csvGenerator;

    public function __construct(
        \Pss\CsvProcessor\Helper\CsvGenerator $csvGenerator,
        $name = null
    )
    {
        parent::__construct();
        $this->csvGenerator = $csvGenerator;
    }

    protected function configure()
    {
        $this->setName('pss:csv:process')
            ->setDescription('Re-generation CSV to compatibility module Wyomind.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->csvGenerator->reGenerateCsv();
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}