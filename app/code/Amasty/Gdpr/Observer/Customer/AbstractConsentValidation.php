<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Observer\Customer;

use Amasty\Gdpr\Model\Consent;
use http\Env\Request;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

abstract class AbstractConsentValidation
{
    /**
     * @var Consent
     */
    private $consent;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Consent $consent,
        Session $session,
        RequestInterface $request
    ) {
        $this->consent = $consent;
        $this->session = $session;
        $this->request = $request;
    }

    /**
     * @param string $param
     *
     * @return void
     */
    protected function confirmConcent($param)
    {
        $customerId = $this->session->getCustomerId();

        if (!$this->request->getParam($param) || !$customerId) {
            return;
        }

        $from = Consent::FROM_CONTACTUS;
        if ('amgdpr_agree_newsletter' == $param) {
            $from = Consent::FROM_SUBSCRIPTION;
        }

        $this->consent->acceptLastVersion($customerId, $from);
    }
}
