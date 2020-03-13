<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Sales\Controller\Adminhtml\Order;
use Magento\Framework\Exception\LocalizedException;
use WeltPixel\GoogleTagManager\lib\Google\Exception;

/**
 * Class AcceptPayment
 * @package Alfa9\Sales\Controller\Adminhtml\Order
 */
class AcceptPayment extends \Magento\Backend\App\Action {
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * AcceptPayment constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var \Magento\Sales\Model\Order $order */
        try {
            $order = $this->orderRepository->get($orderId);
        }catch (Exception $exception) {
            $order = null;
        }
        if($order) {
            try {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);
                /** @var \Magento\Framework\DB\Transaction $transactionSave */
                $transactionSave = $this->_objectManager->create(
                    \Magento\Framework\DB\Transaction::class
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                $this->messageManager->addSuccessMessage('Create the invoice successfully');
                /** @var \Magento\Sales\Model\Order\Status\History $comment */
                $comment = $order->addCommentToStatusHistory(__('Pago aceptado y notificacion enviada al cliente.'));
                $comment->setIsVisibleOnFront(true);
                $comment->setIsCustomerNotified(true);
                $comment->save();
                try {
                    $this->invoiceSender->send($invoice);
                    /*$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->orderRepository->save($order);
                    */
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $this->messageManager->addErrorMessage(__('Unable to change the status of the order'));
                }

            }catch (\Exception $exception) {
                $this->logger->critical($exception);
                $this->messageManager->addErrorMessage('There are some problems trying to accept the payment');
            }
        } else {
            $this->messageManager->addErrorMessage('Invalid order');
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/*/view', ['order_id' => $orderId]);
    }

    /**
     * {@inheritdoc}
     */
    public function _isAllowed() {
        return $this->_authorization->isAllowed('Alfa9_Sales::accept_payment');
    }
}