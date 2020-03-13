<?php
namespace PSS\CRM\Api;

interface TicketRepositoryInterface
{

    /**
     * Returns information by user email
     *
     * @api
     * @param string $email User Email
     * @return mixed
     */
    public function get($email);

    /**
     * Get the Points
     *
     * @api
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getPoints($quote);
}
