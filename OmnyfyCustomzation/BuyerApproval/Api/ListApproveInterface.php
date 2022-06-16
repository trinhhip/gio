<?php
namespace OmnyfyCustomzation\BuyerApproval\Api;

/**
 * Interface ListApproveInterface
 *
 * @package OmnyfyCustomzation\BuyerApproval\Api
 */
interface ListApproveInterface
{
    /**
     * Approve customer
     *
     * @param string $email
     *
     * @return string
     */
    public function approveCustomer($email);

    /**
     * Not Approve customer
     *
     * @param string $email
     *
     * @return string
     */
    public function notApproveCustomer($email);
}
