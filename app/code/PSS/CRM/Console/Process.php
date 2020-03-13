<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \PSS\CRM\Cron\Queue\Process as ProcessQueue;
use \Magento\Framework\App\State;

class Process extends Command
{

    protected $queue;

    protected $state;

    public function __construct(ProcessQueue $queue, State $state) 
    {
        $this->state = $state;
        $this->queue = $queue;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('alfa9:process-queue')
            ->setDescription('Process queue row');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*$this->state->setAreaCode('frontend');
        $this->queue->execute();
        $output->writeln('Processed!');*/
    }
}