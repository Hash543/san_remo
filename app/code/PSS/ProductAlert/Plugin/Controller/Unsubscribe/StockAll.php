<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Plugin\Controller\Unsubscribe;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class StockAll {
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \PSS\ProductAlert\Model\UrlHash
     */
    private $urlHash;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    private $stockCollectionFactory;
    /**
     * Unsubscribe constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \PSS\ProductAlert\Model\UrlHash $urlHash
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \PSS\ProductAlert\Model\UrlHash $urlHash,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory
    ) {
        $this->request = $request;
        $this->urlHash = $urlHash;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
    }

    /**
     * @param \Magento\ProductAlert\Controller\Unsubscribe\StockAll $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundDispatch(
        \Magento\ProductAlert\Controller\Unsubscribe\StockAll $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        //remove alerts for guests
        if (!$this->urlHash->check($this->request)) {
            return $proceed($request);
        }

        //$productId = $this->request->getParam('product_id');
        $email = $this->request->getParam('email');

        try {
            $collection = $this->stockCollectionFactory->create();
            $collection->addFieldToFilter('email', ['eq' => $email]);
            if ($collection->getSize()) {
                $collection->walk('delete');

                $this->messageManager->addSuccessMessage(
                    __('You will no longer receive stock alerts.')
                );
            }
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage(__('The product was not found.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl('/');

        return $resultRedirect;
    }
}
