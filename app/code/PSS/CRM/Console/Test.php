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
use Magento\Framework\Event\Observer;
use \PSS\CRM\Cron\Queue\Process as ProcessQueue;
use \PSS\CRM\Observer\UserService\Authenticated as Query;
use \Magento\Framework\App\State;

class Test extends Command
{

    protected $query;

    protected $state;

    public function __construct(Authenticated $query, State $state)
    {
        $this->state = $state;
        $this->query = $query;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('alfa9:check-ws')
            ->setDescription('Test WS CRM');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('frontend');
        $this->query->execute();
        $output->writeln('Processed!');
    }
}