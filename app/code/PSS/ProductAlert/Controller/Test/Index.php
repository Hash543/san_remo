<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Controller\Test;


use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action {

    /**
     * @var \PSS\ProductAlert\Model\Observer
     */
    protected $observer;

    /**
     * Index constructor.
     * @param Context $context
     * @param \PSS\ProductAlert\Model\Observer $observer
     */
    public function __construct(
        Context $context,
        \PSS\ProductAlert\Model\Observer $observer
    ) {
        $this->observer = $observer;
        parent::__construct($context);
    }

    public function execute() {
        //die("murio aqui");
        $this->observer->process();
    }
}