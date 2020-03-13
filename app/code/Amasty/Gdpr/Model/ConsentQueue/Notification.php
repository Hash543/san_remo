<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ConsentQueue;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class Notification
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ManagerInterface $messageManager,
        UrlInterface $url
    ) {
        $this->messageManager = $messageManager;
        $this->url = $url;
    }

    /**
     * Add notice message with link to email queue grid
     */
    public function addQueueLinkNotice()
    {
        $queueGridLink = $this->url->getUrl('amasty_gdpr/consentQueue/index');

        $this->messageManager->addComplexNoticeMessage(
            'addConsentQueueAddNoticeMessage',
            [
                'referer' => $queueGridLink
            ]
        );
    }
}
