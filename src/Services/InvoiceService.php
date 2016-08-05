<?php

namespace Powon\Services;
/**
 * Class InvoiceService
 * @package Powon\services
 */
use Powon\Entity\Invoice;


interface InvoiceService
{
    /**
     * @param int $invoice_id
     * @return Invoice
     */
    public function getInvoiceById($invoice_id);

    /**
     * @param int $member_id
     * @return Invoice[] of invoice entities.
     */

    public function getInvoiceByMember($member_id);

    /**
     * @return Invoice[] of invoice entities.
     */

    public function getUnpaidInvoices();

}