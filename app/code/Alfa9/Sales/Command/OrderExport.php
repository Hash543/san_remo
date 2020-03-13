<?php
/**
 * OrderExport
 *
 * @copyright Copyright © 2019 Alfa9 Servicios Web, S.L.. All rights reserved.
 * @author    xsanz@alfa9.com
 */

namespace Alfa9\Sales\Command;


use Magento\Framework\App\State;
use Magento\Framework\Event\Manager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrderExport extends Command
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var State
     */
    private $state;

    private $eventManager;

    public function __construct(
        State $state,
        OrderRepositoryInterface $orderRepository,
        Manager $eventManager,
        $name = null
    )
    {
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
        parent::__construct($name);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output)
    {

        $this->state->setAreaCode('adminhtml');

        $orderId = $input->getArgument('order_id');

        if($orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
                if ($order->getId()) {
                    //DISPATCH EVENT TO GENERATE CSV
                    $this->eventManager->dispatch('pss_sales_order_export_command', ['order' => $order]);

                }
                $output->writeln('ORDER EXPORT FINISHED');
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('pss:order:export')
            ->setDescription('generate CSV order and customer')
            ->addArgument(
                'order_id',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Order Id (not increment id)',
                null
            );
    }


}